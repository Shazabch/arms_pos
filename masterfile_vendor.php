<?
/*
REVISION HISTORY
================

1/8/2008 4:20:05 PM gary
- add term column in branch_vendor.

6/17/2010 3:38:07 PM Alex
- add vendor block branch

7/22/2010 11:13:33 AM Alex
- fix bugs on storing block list

11/1/2010 4:55:56 PM Justin
- Added Allow GRN without PO field.
- Added config check as if without config means no update or insert for this field.

5/23/2011 5:05:22 PM Justin 
- Added export as CSV format feature (all vendors with no filter).

6/24/2011 4:55:00 PM Andy
- Make all branch default sort by sequence, code.

9/5/2011 5:22:08 PM Andy
- Add "Enable Stock Reorder Notify" checkbox at vendor masterfile.
- Add maintenance version checking.

12/13/2011 12:57:56 PM Andy
- Add when update vendor discount table, system will also update all related SKU cost price.

4/30/2012 4:58:56 PM Andy
- Add can filter by "All".

5/2/2012 10:20:02 AM Andy
- Change vendor list to sort by description.
- Add escape string when export csv.

7/5/2012 1:33 PM Andy
- Add can generate vendor portal key for vendor at vendor master file.

7/16/2012 5:09 PM Andy
- Add can select sku group for each access branch.

7/17/2012 12:00:23 PM Justin
- Added to update account ID if found config.
- Added to missing update for GRN without PO by branch.

8/2/2012 10:28 AM Andy
- Add when update vendor trade discount table system will auto keep a history.

8/11/2012 yinsee
- add sales profit %

8/13/2012 4:59: PM Andy
- add link to debtor.

9/11/2012 3:24 PM Andy
- Enhance vendor login ticket,email, link to debtor to saved by branch.

4/12/2013 10:22 AM Justin
- Added Allow GRN items qty not over PO qty field.
- Enhanced to update or insert for this field while found is grn future.

5/22/2013 11:51 AM Justin
- Added Allow PO without checkout GRA field.

10/1/2013 6:01 PM Fithri
- make all email field not compulsary
- when sending email, check to trigger send function only when email is set

10/23/2013 9:47 AM Fithri
- records is now displayed in pages, 20 per page
- re-arrange default filters behaviours

12/23/2013 5:02 PM Justin
- Bug fixed on field changes capture for log is not working properly while having more than 1 form.

2/5/2014 11:39 AM Fithri
- add more / missing details in user log

8/21/2014 4:03 PM Justin
- Enhanced to have GST Type, Registration Number & Start Date.

10/28/2014 1:40 PM Justin
- Enhanced to have validation for GST Type, Registration Number & Start Date (exceipt tax code "NR").

10/30/2014 10:45 AM Justin
- Enhanced to take out GST Type and replace with "GST Registered" checkbox.
- Enhanced to add config checking while loading gst list.

1/24/2015 1:05 PM Justin
- Enhanced the "GST Registered" into drop down list.

3/4/2015 2:51 PM Andy
- Remove "Account Receivable Code" and "Account Receivable Name".

12/1/2016 11:46 AM Andy
- Add Internal Code. (Use for GRR for IBT DO Checking)
- Increase maintenance version checking to 304.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

8/27/2018 11:45 AM Andy
- Add SST feature.
- Increase maintenance version checking to 357.

12/2/2020 3:34 PM Andy
- Fixed update vendor trade discount table bugs.

01/5/2021 4:44 PM Rayleen
- Add delivery_type in vendor query

1/8/2021 2:10 PM Andy
- Increase maintenance version checking to 482.

*/
include("include/common.php");
if (!$login){
	if(is_ajax()){
		die($LANG['YOU_HAVE_LOGGED_OUT']);
	}else{
		js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
	}
}
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

$maintenance->check(482);

$smarty->assign("PAGE_TITLE", "VENDOR Master File");

if (isset($_REQUEST['a']))
{
	$id = intval($_REQUEST['id']);
	switch ($_REQUEST['a'])
	{
	    case 'ajax_reload_table':
	        load_table();
	        exit;
	    
	    case 'load_vvc':
			$form=$_REQUEST;
			$id=$form['vendor_id'];	    
		    $con->sql_query("select description, acct_code from vendor where id = $id");
		    $r = $con->sql_fetchrow();
			$form['acct_code'] = unserialize($r['acct_code']);
			$form['vendor']=$r['description'];
			//echo"<pre>";print_r($form['acct_code']);echo"</pre>";
			$con->sql_query("select id, code, description from branch");
			$smarty->assign("branches", $con->sql_fetchrowset());
			$smarty->assign("vvc", $form);	
			$smarty->display("masterfile_vendor_index.vvc.tpl");				    	
	    	exit;
	        
	    case 'vvc_keyin':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			//exit;
			$form=$_REQUEST;
			$id=$form['vendor_id'];	 
			$acct_code = serialize($form['acct_code']);
									
			$con->sql_query("update vendor set acct_code='$acct_code' where id=$id");
			
			print "<script>alert('$LANG[PAYMENT_VOUCHER_VENDOR_MAINTENANCE_UPDATED]');</script>";	
			exit;

	    case 'load_vbb':
			$form=$_REQUEST;
			$type=array('PO','GRR');
			$id=$form['vendor_id'];
		    $con->sql_query("select description, po_block_list, grr_block_list from vendor where id = $id");
		    $r = $con->sql_fetchrow();
			$block[] = unserialize($r['po_block_list']);
			$block[] = unserialize($r['grr_block_list']);
			$form['vendor']=$r['description'];
			$con->sql_query("select id, code, description from branch");
			$smarty->assign("branches", $con->sql_fetchrowset());
			$smarty->assign("vbb", $form);
			$smarty->assign("type", $type);
			$smarty->assign("block", $block);
			$smarty->display("masterfile_vendor_index.vbb.tpl");
	    	exit;

		case 'vbb_keyin':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			//exit;
			$form=$_REQUEST;
			$id=$form['vendor_id'];
			$tbl_col=array('po_block_list','grr_block_list');
            $con->sql_query("update vendor set ".$tbl_col[0]."='' , ".$tbl_col[1]."='' where id=$id");
			if ($form['block']){
				foreach ($form['block']	as $no => $type_block_list){
					$sblock = serialize($type_block_list);
	                $con->sql_query("update vendor set ".$tbl_col[$no]."='$sblock' where id=$id");
				}
			}
 			print "<script>alert('Vendor Block List Updated');</script>";
			exit;

		// load trade discount table
		case 'load_td':
			$did = intval($_REQUEST['department_id']);
		    $con->sql_query("select branch_id, department_id, skutype_code, rate from vendor_commission where vendor_id = $id and department_id = $did");		    
		    $form['vendor_id'] = $id;
		    $form['department_id'] = $did;
			$ff = array("vendor_id", "department_id");		    
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
		    if (!privilege('MST_VENDOR')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VENDOR', BRANCH_CODE), "/index.php");
			
			$sql = '';
		    //print_r($_REQUEST);
		    $id = intval($_REQUEST['vendor_id']);
		    $did = intval($_REQUEST['department_id']);
		    $added = date("Y-m-d H:i:s");
		    
			$con->sql_begin_transaction();
			
			$con->sql_query("select id,code,description from vendor where id=$id");
			$vendor = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			foreach ($_REQUEST['commission'] as $k => $f2)
			{
			    foreach ($f2 as $bid => $v)
			    {
					//if ($sql != '') $sql .= ",";
					//$sql .= "(" . mi($bid) . ", $id, $did, " . ms($k) . ", " . mf($v) . ")";
					
					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['vendor_id'] = $id;
					$upd['department_id'] = $did;
					$upd['skutype_code'] = $k;
					$upd['rate'] = mf($v);
					
					regen_sku_vendor_commision_cost($upd);	// update sku item cost
					$con->sql_query("replace into vendor_commission ".mysql_insert_by_field($upd));
					
					create_vendor_commission_history($upd);	// store vendor commission history
				}
			}
			//$con->sql_query("replace into vendor_commission (branch_id, vendor_id, department_id, skutype_code, rate) values $sql");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, 'Vendor update trade discount table for ' . $vendor['code']);
			
			$con->sql_commit();
			
			print "<script>parent.window.hidediv('ddiv');\nalert('$LANG[MSTVENDOR_TRADE_DISCOUNT_UPDATED]');</script>";
			exit;
			
		// add record
		case 'a':
			if (!privilege('MST_VENDOR')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VENDOR', BRANCH_CODE), "/index.php");

			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				$arr = array('code', 'prefix_code','description', 'company_no', 'vendortype_code', 'term', 'prompt_payment_term', 'prompt_payment_discount', 'fast_payment_term', 'fast_payment_discount', 'allow_grr_without_po', 'grace_period', 'credit_limit', 'bank_account', 'address', 'phone_1', 'phone_2', 'phone_3', 'contact_person', 'contact_email','enable_stock_reoder_notify', 'allow_po_without_checkout_gra', 'gst_register_no', 'gst_start_date', 'gst_register','account_payable_name','account_payable_code','internal_code', 'delivery_type');

				// exclude the insert for allow GRN without PO field if not using grn future				
				if($config['use_grn_future']){
					$arr[] = 'allow_grn_without_po'; 
					$arr[] = 'grn_qty_no_over_po_qty';
				}

				if($config['enable_vendor_account_id']){
					$arr[] = 'account_id';
				}
				
				$code = $form['code'];
				$form = mysql_insert_by_field($form, $arr);

				$con->sql_query("insert into vendor ".$form);
				$id = $con->sql_nextid();
				log_br($sessioninfo['id'], 'MASTERFILE', $id, 'Vendor create ' . $code);
				//load_table();
				print "<script>parent.window.hidediv('ndiv');parent.window.reload_table(true);alert('$LANG[MSTVENDOR_NEW_RECORD_ADDED]');</script>\n";
			}
			exit;
			
		// update vendor per branch record
		case 'av':
			if (!privilege('MST_VENDOR')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VENDOR', BRANCH_CODE), "/index.php");

			$form = $_REQUEST;
			//print_r($form);
			//exit;
			$errmsg = validate_data($form, true);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg, 'vmsg');
			}
			else
			{
				$con->sql_query("replace into branch_vendor " . mysql_insert_by_field($form, array('vendor_id', 'branch_id', 'allow_grr_without_po', 'allow_grn_without_po', 'credit_limit', 'bank_account', 'address', 'phone_1', 'phone_2', 'phone_3', 'contact_person', 'contact_email', 'term', 'account_id')));
				
				foreach (preg_split("/\|/", $_REQUEST["changed_fields"]) as $ff)
				{
					// strip array
					$ff = preg_replace("/\[.*\]/", '', $ff);
					if ($ff != "") $uqf[$ff] = 1;
				}
				
				$changes .= "\nEdited fields: (" . join(", ", array_keys($uqf)) . ")";

				log_br($sessioninfo['id'], 'MASTERFILE', $form['vendor_id'], "Vendor update information (".mi($form['vendor_id']).", ".mi($form['branch_id']).")".$changes);
				//load_table();
				print "<script>parent.window.reload_table(true);alert('$LANG[MSTVENDOR_DATA_UPDATED]');</script>\n";
			}
			exit;

		case 'lv':
			$vid = intval($_REQUEST['vid']);
			$con->sql_query("select * from branch_vendor where vendor_id = $vid and branch_id = $id");
			IRS_fill_form("f_t", array('_branch_id', 'vendor_id', 'allow_grr_without_po', 'allow_grn_without_po',  'credit_limit', 'bank_account', 'address', 'phone_1', 'phone_2', 'phone_3', 'contact_person', 'contact_email', 'term', 'account_id'), $con->sql_fetchrow(), 'vloaded()');
			exit;
			
		case 'e':
			if (!privilege('MST_VENDOR')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VENDOR', BRANCH_CODE), "/index.php");

			$con->sql_query("select * from vendor where id = $id");
			if ($con->sql_numrows()<=0)
			{
				print "<script>alert('Invalid vendor ID: $id');</script>\n";
				exit;
			}
			$form = $con->sql_fetchrow();
			$con->sql_freeresult();

			$fill_data = array('code', 'prefix_code', 'description', 'company_no', '_vendortype_code', 'term', 'prompt_payment_term', 'prompt_payment_discount', 'fast_payment_term', 'fast_payment_discount', 'grace_period', 'credit_limit', 'bank_account', 'allow_grr_without_po', 'allow_grn_without_po', 'address', 'phone_1', 'phone_2', 'phone_3', 'contact_person', 'contact_email', 'account_id', 'grn_qty_no_over_po_qty', 'allow_po_without_checkout_gra', 'gst_register_no', 'gst_start_date','account_payable_name','account_payable_code','internal_code', "tax_register", "tax_percent", "delivery_type");
			if($form['enable_stock_reoder_notify'])	$fill_data[] = 'enable_stock_reoder_notify';
			if($form['gst_register'])	$fill_data[] = 'gst_register';
			
			IRS_fill_form("f_b", $fill_data, $form);
			exit;

		case 'v':
			if (!privilege('MST_VENDOR')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VENDOR', BRANCH_CODE), "/index.php");

			$con->sql_query("update vendor set active = ".mb($_REQUEST['v'])." where id = $id");
			$con->sql_query("select code from vendor where id = $id");
			$v1 = $con->sql_fetchassoc();
			if ($_REQUEST['v'])
				log_br($sessioninfo['id'], 'MASTERFILE', $id, 'Vendor activate ' . $v1['code']);
			else
				log_br($sessioninfo['id'], 'MASTERFILE', $id, 'Vendor deactivate ' . $v1['code']);
			print "<script>parent.window.reload_table(true);</script>"; //load_table();
			exit;

		case 'u':
			if (!privilege('MST_VENDOR')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VENDOR', BRANCH_CODE), "/index.php");

			$form = $_REQUEST;
			$errmsg = validate_data($form);

			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				$arr = array('code', 'prefix_code', 'description', 'company_no', 'vendortype_code', 'term', 'prompt_payment_term', 'prompt_payment_discount', 'fast_payment_term', 'fast_payment_discount', 'allow_grr_without_po', 'grace_period', 'credit_limit', 'bank_account', 'address', 'phone_1', 'phone_2', 'phone_3', 'contact_person', 'contact_email','enable_stock_reoder_notify', 'allow_po_without_checkout_gra', 'gst_register_no', 'gst_start_date', 'gst_register','account_payable_name','account_payable_code','internal_code', 'tax_register', 'tax_percent', 'delivery_type');

				// exclude the insert for allow GRN without PO field if not using grn future				
				if($config['use_grn_future']){
					$arr[] = 'allow_grn_without_po'; 
					$arr[] = 'grn_qty_no_over_po_qty';
				}

				if($config['enable_vendor_account_id']){
					$arr[] = 'account_id';
				}
				$code = $form['code'];
				$form = mysql_update_by_field($form, $arr);

				$con->sql_query("update vendor set ".$form." where id = $id");

				if ($con->sql_affectedrows()>0)
				{
					// code changed
					$changes = "";
					foreach (preg_split("/\|/", $_REQUEST["changed_fields"]) as $ff)
					{
						// strip array
						$ff = preg_replace("/\[.*\]/", '', $ff);
						if ($ff != "") $uqf[$ff] = 1;
					}
					
					$changes .= "\nEdited fields: (" . join(", ", array_keys($uqf)) . ")";

					log_br($sessioninfo['id'], 'MASTERFILE', $id, 'Vendor update information ' . "($code)" . $changes);
					//load_table();
					// saved. back to front page
					print "<script>parent.window.hidediv('ndiv');parent.window.reload_table(true);alert('$LANG[MSTVENDOR_DATA_UPDATED]');</script>";
				}
				else
					print "<script>parent.window.hidediv('ndiv');alert('$LANG[NO_CHANGES_MADE]');</script>";
			}
			exit;
		case 'do_export':
			global $con;
			$contents = array();

			$con->sql_query("select v.* from vendor as v order by v.description");

			if($con->sql_numrows() > 0){
				$contents[] = "Code,Company Name,Address 1,Address 2,Address 3,Address 4,Tel,Fax,Contact Person,Terms\r\n";

				while($r=$con->sql_fetchrow()){
					if($r['address']){
						$add = explode("\n", $r['address']);

						$r['add_1'] = '"'.$add[0].'"';
						$r['add_2'] = '"'.$add[1].'"';
						$r['add_3'] = '"'.$add[2].'"';
						//$r['add_4'] = '"'.$add[3].'"';
						
						if(count($add) > 4){
							foreach($add as $row=>$val){
								if($row >= 3){
									$add_4 .= $val;
								}
							}
							$r['add_4'] = '"'.$add_4.'"';
						}else{
							$r['add_4'] = '"'.$add[3].'"';
						}
					}
					$contents[] = "\"$r[code]\",\"$r[description]\",$r[add_1],$r[add_2],$r[add_3],$r[add_4],\"$r[phone_1]\",\"$r[phone_3]\",\"$r[contact_person]\",$r[term]\r\n";
				}

				$content = join("", $contents);
				header("Content-type: text/plain");
				header('Content-Disposition: attachment;filename=vendor_list.CSV');
				print $content;
			}else{
				print "No data found.";
			}
			exit;
		case 'ajax_load_vendor_portal_info':
			load_vendor_portal_info();
			exit;
		case 'ajax_update_vendor_portal':
			ajax_update_vendor_portal();
			exit;
		case 'setup_vendor_portal_branch_info':
			setup_vendor_portal_branch_info();
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

$con->sql_query("select * from vendortype");
$smarty->assign("vendortype", $con->sql_fetchrowset());
$con->sql_query("select id, code, description from branch");
$smarty->assign("branches", $con->sql_fetchrowset());
$con->sql_query("select id,code from trade_discount_type order by code");
$smarty->assign("skutype", $con->sql_fetchrowset());

if($config['enable_gst']){
	$q1 = $con->sql_query("select * from gst where active=1 and type='purchase' and is_vd_special_code = 1 order by id");
	while($r = $con->sql_fetchassoc($q1)){
		$gst_list[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);
	$smarty->assign("gst_list", $gst_list);
}
/*
$con->sql_query("select v.*, t.description as vendortype_description from vendor as v left join vendortype as t on v.vendortype_code = t.code order by v.code limit 0,20");
$smarty->assign("vcount", $con->sql_numrows());
$smarty->assign("vendors", $con->sql_fetchrowset());
*/
load_table(true);
$smarty->display("masterfile_vendor_index.tpl");

function load_table($sql_only = false)
{
	global $con, $smarty;
	/*
	if (isset($_REQUEST['search']))
	{
	    $opt = "where v.$_REQUEST[search] like ".ms($_REQUEST['value'].'%')." or v.$_REQUEST[search] like ".ms('% '.$_REQUEST['value'].'%');
	}
	else
	{
	    $o = strval($_REQUEST['alphabate']);
	    
		if ($o == '')
		{
			$opt = 'where v.active=0';
		}
		elseif ($o == 'others')
		{
			$opt = "where v.description < 'A' or v.description > 'Zz'";
		}
		elseif($o == 'all'){
			// all no need filter
		}else{
			$opt = "where v.description like " .ms($o.'%');
		}
	}
	*/
	
	$filter = array();
	if ($_REQUEST['desc']) {
		$filter[] = "v.description like ".ms('%'.replace_special_char($_REQUEST['desc']).'%');
	}
	if (isset($_REQUEST['status']) && $_REQUEST['status'] != '') {
		$filter[] = "v.active = ".mi($_REQUEST['status']);
	}
	if ($_REQUEST['starts_with']) {
		if ($_REQUEST['starts_with'] == 'others') $filter[] = "v.description < 'A' or v.description > 'Zz'";
		else $filter[] = "v.description like ".ms($_REQUEST['starts_with'].'%');
	}
	$filter_str = $filter ? 'where '.join(' and ', $filter) : '';
	
	$start_at = $_REQUEST['pg'] ? mi($_REQUEST['pg'])-1 : 0;
	$start_at = $start_at*20;
	
	$con->sql_query("select v.*, t.description as vendortype_description from vendor as v left join vendortype as t on v.vendortype_code = t.code $filter_str order by v.description limit $start_at,20");
	$smarty->assign("vendors", $con->sql_fetchrowset());
	
	$con->sql_query("select count(*) as c from vendor v $filter_str");
	$rows = $vcount = mi($con->sql_fetchfield(0));
	$smarty->assign("vcount", $vcount);
	
	$pagination = '';
	for ($p=1; $rows>0; $p++) {
		$selected = ($p == mi($_REQUEST['pg'])) ? 'selected' : '';
		$pagination .= "<option $selected value=\"$p\">$p</option>";
		$rows -= 20;
	}
	$smarty->assign("total_page", --$p);
	$smarty->assign("pagination", $pagination);
	
	if (!$sql_only) $smarty->display("masterfile_vendor_table.tpl");
}


function validate_data(&$form, $is_branch_vendor = false)
{
	global $LANG, $con, $id;

	$errm = array();
	
	if ($is_branch_vendor)
	{
		// nothing to check...
		if (intval($form['vendor_id']) == 0)
		{
			$errm[] = $LANG['MSTVENDOR_INVALID_VENDOR'];
		}
		if (intval($form['branch_id']) == 0)
		{
			$errm[] = $LANG['MSTVENDOR_INVALID_BRANCH'];
		}
	}
	else
	{
		if ($form['code'] == '') 
		{
			$errm[] = $LANG['MSTVENDOR_CODE_EMPTY'];
			return $errm;
		}
		
		$form['code'] = strtoupper($form['code']);
		// if old code != new code, check new code exists
		$con->sql_query("select * from vendor where id <> $id and code = " . ms($form['code']));
		if ($con->sql_numrows() > 0)
		{
			$errm[] = sprintf($LANG['MSTVENDOR_CODE_DUPLICATE'], $form['code']);
		}
		if ($form['description'] == '')
			$errm[] = $LANG['MSTVENDOR_DESCRIPTION_EMPTY'];

		if ($form['company_no'] == '')
			$errm[] = $LANG['MSTVENDOR_COMPANY_NO_EMPTY'];

		if ($form['term'] == '')
			$errm[] = $LANG['MSTVENDOR_TERM_EMPTY'];
		
		// check internal code
		if($form['internal_code']){
			$form['internal_code'] = strtoupper($form['internal_code']);
			
			$con->sql_query("select * from vendor where id <> $id and internal_code = " . ms($form['internal_code']));
			if ($con->sql_numrows() > 0)
			{
				$errm[] = sprintf($LANG['MSTVENDOR_INTERNAL_CODE_DUPLICATE'], $form['internal_code']);
			}
			$con->sql_freeresult();
		}
  }
  
	if ($form['credit_limit'] == '')
		$errm[] = $LANG['MSTVENDOR_CREDIT_LIMIT_EMPTY'];

	if ($form['bank_account'] == '')
		$errm[] = $LANG['MSTVENDOR_BANK_ACCOUNT_EMPTY'];

	if ($form['address'] == '')
		$errm[] = $LANG['MSTVENDOR_ADDRESS_EMPTY'];

	if ($form['contact_person'] == '')
		$errm[] = $LANG['MSTVENDOR_CONTACT_PERSON_EMPTY'];

	/*if ($form['contact_email'] == '')
		$errm[] = $LANG['MSTVENDOR_EMAIL_EMPTY'];*/

	if ($form['phone_1'] == '')
		$errm[] = $LANG['MSTVENDOR_PHONE_1_EMPTY'];

	if ($form['contact_email'] && !preg_match(EMAIL_REGEX, $form['contact_email']))
		$errm[] = $LANG['MSTVENDOR_INVALID_EMAIL'];

	// check if found the vendor is set to use tax code that is not "NR" and left empty GST start date or registration no, show error
	if($form['gst_register'] == -1 && (!$form['gst_start_date'] || !$form['gst_register_no'])){
		$errm[] = $LANG['MSTVENDOR_INVALID_GST_INFO'];
	}
		
	return $errm;
}

function regen_sku_vendor_commision_cost($params){
	global $con, $config;
	
	$bid = mi($params['branch_id']);
	$vendor_id = mi($params['vendor_id']);
	$dept_id = mi($params['department_id']);
	$skutype_code = trim($params['skutype_code']);
	$rate = mf($params['rate']);
	$force_update = mi($_REQUEST['force_update']);
	
	if(!$force_update){	// not force, check current rate first
		$con->sql_query("select rate 
		from vendor_commission 
		where branch_id=$bid and vendor_id=$vendor_id and department_id=$dept_id and skutype_code=".ms($skutype_code));
		$current_rate = mf($con->sql_fetchfield(0));
		$con->sql_freeresult();
		
		if($rate == $current_rate)	return;	// no need update
	}
	
	$filter = array();
	$filter[] = "sku.trade_discount_type=2 and sku.sku_type='CONSIGN' and sku.default_trade_discount_code=".ms($skutype_code);
	$filter[] = "sku.apply_branch_id=$bid";
	$filter[] = "sku.vendor_id=$vendor_id";
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

function load_vendor_portal_info(){
	global $con, $smarty, $sessioninfo, $config, $LANG;
	
	if(!$config['enable_vendor_portal'])	die($LANG['NEED_CONFIG']);
	
	$vid = mi($_REQUEST['vid']);
	
	if(!$vid)	die("Invalid ID");
	
	$con->sql_query("select v.id, v.code, v.description, v.active_vendor_portal, vpi.*
	from vendor v 
	left join vendor_portal_info vpi on vpi.vendor_id=v.id
	where v.id=$vid");
	$form = $con->sql_fetchassoc();
	$form['allowed_branches'] = unserialize($form['allowed_branches']);
	$form['sku_group_info'] = unserialize($form['sku_group_info']);
	$form['sales_report_profit'] = unserialize($form['sales_report_profit']);
	
	$con->sql_freeresult();
	
	if(!$form)	die("Vendor information not found.");
	
	// load branches list
	$branches_list = array();
	$con->sql_query("select id,code from branch where active=1 order by sequence,code");
	while($r = $con->sql_fetchassoc()){
		$branches_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	// load sku group list
	$sku_group_list = array();
	$con->sql_query("select * from sku_group order by code,description");
	while($r = $con->sql_fetchassoc()){
		$sku_group_list[] = $r;
	}
	$con->sql_freeresult();
	
	// load debtor list
	$debtor_list = array();
	$con->sql_query("select id,code,description from debtor where active=1 order by code");
	while($r = $con->sql_fetchassoc()){
		$debtor_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	// get info by branch
	$form['branch_info'] = array();
	$con->sql_query("select * from vendor_portal_branch_info where vendor_id=$vid");
	while($r = $con->sql_fetchassoc()){
		$form['branch_info'][$r['branch_id']] = $r;
	}
	$con->sql_freeresult();
	
	$smarty->assign('branches_list', $branches_list);
	$smarty->assign('sku_group_list', $sku_group_list);
	$smarty->assign('debtor_list', $debtor_list);
	$smarty->assign('form', $form);
	$ret = array('ok'=>1);
	$ret['html'] = $smarty->fetch('masterfile_vendor.vendor_portal_popup.tpl');
	
	print json_encode($ret);
}

function ajax_update_vendor_portal(){
	global $con, $smarty, $sessioninfo, $config, $LANG;
	
	//print_r($_REQUEST);
	//exit;
	if(!$config['enable_vendor_portal'])	die($LANG['NEED_CONFIG']);
	
	$vid = mi($_REQUEST['vendor_id']);
	$active_vendor_portal = mi($_REQUEST['active_vendor_portal']);
	$allowed_branches = $_REQUEST['allowed_branches'];
	//$use_last_grn = mi($_REQUEST['use_last_grn']);
	$login_ticket = $_REQUEST['login_ticket'];
	$expire_date = $_REQUEST['expire_date'];
	$no_expire = $_REQUEST['no_expire'];
	$sku_group_info = $_REQUEST['sku_group_info'];
	$sales_report_profit = $_REQUEST['sales_report_profit'];
	$link_debtor_id = $_REQUEST['link_debtor_id'];
	$contact_email = $_REQUEST['contact_email'];
	
	$con->sql_query("select v.id, v.code, v.description, v.active_vendor_portal, vpi.vendor_id as vpi_vid, vpi.*
	from vendor v 
	left join vendor_portal_info vpi on vpi.vendor_id=v.id
	where v.id=$vid");
	$form = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$form)	die("Vendor information not found.");
	
	$upd = array();
	$upd['active_vendor_portal'] = $active_vendor_portal;
	$con->sql_query("update vendor set ".mysql_update_by_field($upd)." where id=$vid");
	
	// check duplicate login ticket
	foreach($login_ticket as $tmp_bid => $ticket){
		if($ticket){	// got ticket
			$con->sql_query("select vendor_id from vendor_portal_branch_info where branch_id=".mi($tmp_bid)." and login_ticket=".ms($ticket)." and vendor_id<>$vid");
			$dup_ticket = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($dup_ticket){
				die("Invalid ticket, Ticket $ticket already use for other vendor in same branch. please regenerate new ticket number.");
			}
		}
		
		// check email
		$email_list_str = trim($contact_email[$tmp_bid]);
		if($email_list_str){
			$email_list = explode(",", $email_list_str);
			foreach($email_list as $tmp_email){
				if (!preg_match(EMAIL_REGEX, trim($tmp_email))){
					die("Invalid email format ($tmp_email).");
				}
			}
		}
	}
	
	$time = date("Y-m-d H:i:s");
	
	$upd = array();
	$upd['last_update'] = $time;
	$upd['last_update_by'] = $sessioninfo['id'];
	//$upd['login_ticket'] = $login_ticket;
	//$upd['use_last_grn'] = $use_last_grn;
	//$upd['expire_date'] = $no_expire ? '9999-12-31' : $expire_date;
	$upd['allowed_branches'] = serialize($allowed_branches);
	$upd['sku_group_info'] = serialize($sku_group_info);
	$upd['sales_report_profit'] = serialize($sales_report_profit);
	//$upd['link_debtor_id'] = $link_debtor_id;
	
	// vendor_portal_info
	if($form['vpi_vid']){	// already hv vendor_portal_info
		$con->sql_query("update vendor_portal_info set ".mysql_update_by_field($upd)." where vendor_id=$vid");
	}else{	// need add new
		$upd['vendor_id'] = $vid;
		$upd['added'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("insert into vendor_portal_info ".mysql_insert_by_field($upd));
	}
	
	// vendor_portal_branch_info
	foreach($sku_group_info as $tmp_bid => $tmp_sgi){
		// get info by branch
		$con->sql_query("select * from vendor_portal_branch_info where vendor_id=$vid and branch_id=".mi($tmp_bid));
		$vpbi = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$upd = array();
		$upd['active'] = mi($allowed_branches[$tmp_bid]) ? 1 : 0;
		$upd['login_ticket'] = $login_ticket[$tmp_bid];
		$upd['expire_date'] = $no_expire[$tmp_bid] ? '9999-12-31' : $expire_date[$tmp_bid];
		$upd['link_debtor_id'] = $link_debtor_id[$tmp_bid];
		$upd['last_update'] = $time;
		$upd['contact_email'] = $contact_email[$tmp_bid];
		
		if(!$vpbi){
			$upd['added'] = $time;
			$upd['branch_id'] = $tmp_bid;
			$upd['vendor_id'] = $vid;
			
			$con->sql_query("insert into vendor_portal_branch_info ".mysql_insert_by_field($upd));
		}else{
			$con->sql_query("update vendor_portal_branch_info set ".mysql_update_by_field($upd)." where vendor_id=$vid and branch_id=".mi($tmp_bid));
		}
	}
	
	log_br($sessioninfo['id'], 'MASTERFILE', $vid, 'Vendor Portal Info Updated: Vendor ID#'.$form['id'].", Vendor Code#$form[code]");
	
	$ret = array();
	$ret['ok'] = 1;
	print json_encode($ret);
}

function create_vendor_commission_history($data){
	global $con;
	
	$branch_id = mi($data['branch_id']);
	$vendor_id = mi($data['vendor_id']);
	$skutype_code = $data['skutype_code'];
	$department_id = mi($data['department_id']);
	$rate = $data['rate'];
	
	if(!$branch_id || !$vendor_id || !$skutype_code || !$department_id)	die("Invalid parameter to create vendor commission history");
	
	$today = date("Y-m-d");
	
	// check whetehr got data changed before
	$con->sql_query("select * from vendor_commission_history where branch_id=$branch_id and vendor_id=$vendor_id and department_id=$department_id and skutype_code=".ms($skutype_code)." and date_from!=".ms($today)." and date_to='9999-12-31'");
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if($tmp){
		$con->sql_query("delete from vendor_commission_history where branch_id=$branch_id and vendor_id=$vendor_id and department_id=$department_id and skutype_code=".ms($skutype_code)." and date_from!=".ms($today)." and date_to='9999-12-31'");
		
		$tmp['date_to'] = date("Y-m-d", strtotime("-1 day", strtotime($today)));
		$con->sql_query("replace into vendor_commission_history ".mysql_insert_by_field($tmp));
	}
	
	$upd = array();
	$upd['branch_id'] = $branch_id;
	$upd['vendor_id'] = $vendor_id;
	$upd['skutype_code'] = $skutype_code;
	$upd['department_id'] = $department_id;
	$upd['rate'] = $rate;
	$upd['date_from'] = $today;
	$upd['date_to'] = '9999-12-31';
	$con->sql_query("replace into vendor_commission_history ".mysql_insert_by_field($upd));
}

function setup_vendor_portal_branch_info(){
	global $con;
	
	$q1 = $con->sql_query("select vpi.*, v.contact_email
	from vendor_portal_info vpi
	join vendor v on v.id=vpi.vendor_id
	order by vpi.vendor_id");
	
	$now = date("Y-m-d H:i:s");
	$updated = 0;
	
	while($vpi = $con->sql_fetchassoc($q1)){
		$vpi['allowed_branches'] = unserialize($vpi['allowed_branches']);
		
		if(!$vpi['allowed_branches'])	continue;
		
		foreach($vpi['allowed_branches'] as $bid){	// loop for each branch id
			$upd = array();
			$upd['vendor_id'] = $vpi['vendor_id'];
			$upd['branch_id'] = $bid;
			$upd['login_ticket'] = $vpi['login_ticket'];
			$upd['expire_date'] = $vpi['expire_date'];
			$upd['link_debtor_id'] = $vpi['link_debtor_id'];
			$upd['contact_email'] = $vpi['contact_email'];
			$upd['added'] = $upd['last_update'] = $now;
			
			$con->sql_query("replace into vendor_portal_branch_info ".mysql_insert_by_field($upd));
			$updated++;
		}
	}
	$con->sql_freeresult($q1);
	
	print "$updated Updated.";
}
?>
