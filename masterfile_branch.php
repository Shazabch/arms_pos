<?
/*
6/9/2008 12:45:58 PM yinsee
- add report-prefix for edit

13/3/2009 2:50:00 PM Andy
- branch add column
	- alter table branch add con_dept_name char(100),add con_terms char(10)
	- alter table branch add con_lost_ci_discount double
	
4/4/2009 9:39:05 AM andy/yinsee
- branch sequence sorting

7/28/2009 4:51:05 PM Andy
- Add column 'ci_allow_edit_selling_price' for branch

4/22/2010 6:13:28 PM Andy
- Add print envelope

4/30/2010 12:21:58 PM Andy
- Print branch discount table exclude inactive branch.

5/24/2010 3:49:04 PM Andy
- Masterfile branch trade discount can insert secondary discount percent using "+".

7/9/2010 3:08:31 PM Andy
- Automatically create all cahce table when add new branch.

11/11/2010 11:41:00 AM Alex
- export discount table to excel
- print branches

12/30/2010 4:11:07 PM Andy
- Add counter limit at branch masterfile, only can change by wsatp.

1/12/2011 5:53:23 PM Andy
- Add create branch_status row once add a new branch.

4/11/2011 3:33:52 PM Justin
- Added new field "Region" onto branch.
- Added the insert/update for "Region" field.

5/19/2011 10:21:30 AM Andy
- Change region array structure and its related module.

5/27/2011 5:04:11 PM Justin
- Modified all the updates and field extract to include new field "deliver_to".

5/31/2011 4:14:59 PM Andy
- Make HQ cannot belongs to any region.
- Add update all sku selling price if branch change region.

6/10/2011 12:03:59 PM Justin
- Fixed the bugs where unable to sort correctly by code/description when there is inactive branches.

6/14/2011 11:26:11 AM Alex
- add new column store branch prefix settings when print monthly report => Consingnment modules only

6/22/2011 2:41:57 PM Andy
- Branch sequences printing add can skip in-active branch.
- Fix printing branch sequence cannot follow the position changed by user.

7/6/2011 12:02:19 PM Andy
- Change split() to use explode()

7/8/2011 4:16:32 PM Andy
- Add can select transporter and branch master file.

12/8/2011 1:41:34 PM Justin
- Fixed bug that unable to capture branch code in log.

3/26/2012 3:02:49 PM Andy
- Reconstruct module structure to use ajax update instead of IRS.

3/27/2012 11:10:47 AM Andy
- Add new setting "Can change price when in-active".

4/5/2012 12:02:38 PM Alex
- fix missing load transporter data

5/16/2012 4:04:34 PM Justin
- Added to update trans_end_date into branch table.

6/15/2012 11:43:12 AM Justin
- Added to update type and debtor_id into branch table.

9/11/2012 5:01:00 PM fithri
- Branch masterfile - Add extra info

9/25/2012 2:08:00 PM Fithri
- when add new branch, all the per-branch settings (eg: selling price, block po, discount %, point) can copy from other branch

4/15/2013 3:18 PM Andy
- Fix bug where active/deactive cannot capture user_id.

5/21/2013 11:55 AM Justin
- Disabled the previous copy settings feature while creating new branch.
- Enhanced to have Copy Settings by Ajax.

10/16/2013 5:07 PM Fithri
- insert log when user change branch trade discount

10/1/2013 6:01 PM Fithri
- make all email field not compulsary
- when sending email, check to trigger send function only when email is set

4/17/2014 5:58 PM Andy
- Record added timestamp when add new branch.

8/8/2014 12:08 PM Justin
- Enhanced to have GST Registration & Start Date.
- Bug fixed on mysql bugs of unable to insert new branch.

1/17/2015 9:57 AM  Andy
- Add GST Interbranch Settings.

3/18/2015 12:01 PM Justin
- Bug fixed on GST start date can assign as "0000-00-00".

3/26/2015 10:26 AM Justin
- Enhanced to have "Export Type" for consignment customers.

4/14/2015 11:17 AM Ding Ren
- add account code
- add account_receivable_code
- add account_receivable_name
- add account_payable_code
- add account_payable_name

5/25/2015 4:03 PM Justin
- Enhanced to allow user to control if wants to change price when change region.

7/24/2015 11:02 AM Joo Chia
- Enhance to allow admin to copy branch setting for Trade Discount, Approval Flow, and Block GRN.

8/17/2016 12:01 PM Andy
- Enhanced copy branch settings to ignore user connection abort.

3/13/2017 11:50 AM Justin
- Enhanced upload branch logo to use the "resize_photo" function.

3/24/2017 10:41 AM Justin
- Enhanced system can only accepts JPG/JPEG for logo upload.
- Enhanced to have validation on logo size of 5mb max only.

4/3/2017 2:51 PM Justin
- Bug fixed on logo can't upload if user straight change the logo extension (from png to jpg).

4/28/2017 15:54 Qiu Ying
- Enhanced to add timezone field and set default to "Asia/Kuala_Lumpur"

5/12/2017 9:33 AM Justin
- Enhanced to check against user privilege that must admin or consignment customer while can use copy settings function.

3/30/2018 4:13 PM HockLee
- Added Integration Code to map branch and debtor.

8/15/2018 2:40 PM Andy
- Increase maintenance version checking to 356.

6/18/2019 9:50 AM William
- Added new is_vertical_logo to update branch setting.
- Added new no company name to update branch setting.

11/22/2019 5:49 PM William
- Add new branch outlet photo, "Branch Operation Time", "Longitude" and "Latitude" to branch.
- Increase maintenance version checking to 429.

01/05/2021 5:23 PM Rayleen
- Add new field warehouse_numnber and warehouse_name in branch table

1/8/2021 2:10 PM Andy
- Increase maintenance version checking to 482.
*/
include("include/common.php");
set_time_limit(0);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_BRANCH')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BRANCH', BRANCH_CODE), "/index.php");
$maintenance->check(482);

$smarty->assign("PAGE_TITLE", "Branch Master File");

if($config['consignment_modules'] && $config['enable_consignment_transport_note'] && $config['enable_transporter_masterfile']){
	$got_use_transporter = true;
}

$allow_add_branch = false;
if($sessioninfo['id']==1 || ($config['consignment_modules'] && $sessioninfo['level']>=9999))	$allow_add_branch = true;
$smarty->assign('allow_add_branch', $allow_add_branch);

if (isset($_REQUEST['a']))
{
	$id = intval($_REQUEST['id']);
	switch ($_REQUEST['a'])
	{
	
	    case 'load_vvc':
			$form=$_REQUEST;
			//echo"<pre>";print_r($form);echo"</pre>";
			$id=$form['branch_id'];	
		    $q1=$con->sql_query("select * from voucher_banker_details order by id");
		    $r1 = $con->sql_fetchrowset($q1);
			$smarty->assign("banker", $r1);
									    
		    $q2=$con->sql_query("select code, vvc_details from branch where id = $id");
		    $r2= $con->sql_fetchrow($q2);
			$vvc = unserialize($r2['vvc_details']);
			$branch_name=$r2['code'];

			$smarty->assign("branch_name", $branch_name);
			$smarty->assign("branch_id", $id);
			$smarty->assign("vvc", $vvc);	
			$smarty->display("masterfile_branch_index.vvc.tpl");				    	
	    	exit;
	        
	    case 'vvc_keyin':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			//exit;
			$temp=array();
			$form=$_REQUEST;
			$id=$form['branch_id'];
			foreach ($form['vvc']['banker_code'] as $k=>$v){
				//echo "$k==$v<br>";
				if($v){
					$temp['vvc']['bank_id'][]=$form['vvc']['bank_id'][$k];
					$temp['vvc']['banker_code'][]=$v;
				}
			}
			//echo"<pre>";print_r($temp['vvc']);echo"</pre>";
			//exit;
			$vvc = serialize($temp['vvc']);			
			$con->sql_query("update branch set vvc_details='$vvc' where id=$id");
			
			print "<script>alert('$LANG[PAYMENT_VOUCHER_VENDOR_MAINTENANCE_UPDATED]');</script>";	
			exit;
			
		/*case 'a':
			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
			    $update_field = array('code', 'report_prefix','description', 'company_no', 'address', 'deliver_to', 'phone_1', 'phone_2', 'phone_3', 'contact_email', 'contact_person','con_dept_name','con_terms','con_lost_ci_discount','ci_allow_edit_selling_price','con_sort_by', 'con_split_artno', 'con_group_category');
			    if($sessioninfo['id']==1)    $update_field[] = 'counter_limit';
			    if($config['masterfile_branch_region']) $update_field[] = 'region';
			    if($got_use_transporter)	$update_field[] = "transporter_id";
			    	
  				$con->sql_query("insert into branch " . mysql_insert_by_field($form, $update_field));
  				$new_bid = $con->sql_nextid();  // get the new branch id
  				
  				// create branch status row
  				$branch_status = array();
  				$branch_status['branch_id'] = $new_bid;
  				$branch_status['lastping'] = '';
  				$con->sql_query("insert into branch_status ".mysql_insert_by_field($branch_status));
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Branch create ' . $form['code']);  // record log

				update_sales_cache($new_bid, -1);    // create all cache table
				
				// change selling price base on region
				if($config['masterfile_branch_region'] && $config['sku_use_region_price'] && $form['region'] != ''){
					branch_change_sku_region_selling($new_bid);
				}
					
				load_table();
				print "<script>parent.window.hidediv('ndiv');\nalert('$LANG[MSTBRANCH_NEW_RECORD_ADDED]');</script>\n";
			}
			exit;
			
		case 'e':
			$con->sql_query("select * from branch where id = $id");
			if ($con->sql_numrows()<=0)
			{
				print "<script>alert('Invalid branch ID: $id');</script>\n";
				exit;
			}
			$form_data = $con->sql_fetchrow();
			$form_field = array("code", "report_prefix", "description", "company_no", "address", "deliver_to", "phone_1", "phone_2", "phone_3", "contact_email", 'contact_person','con_dept_name','con_terms','con_lost_ci_discount','con_sort_by', 'counter_limit');
			if($form_data['ci_allow_edit_selling_price'])   $form_field[] = 'ci_allow_edit_selling_price';
			if($form_data['con_split_artno'])   $form_field[] = 'con_split_artno';
			if($form_data['con_group_category'])   $form_field[] = 'con_group_category';
			if($form_data['region'])   $form_field[] = 'region';
			if($got_use_transporter && $form_data['transporter_id'])	$form_field[] = 'transporter_id';
			
			irs_fill_form("f_b", $form_field, $form_data);
			exit;
			
		case 'v':
			$con->sql_query("update branch set active = ".mb($_REQUEST['v'])." where id = $id");
			$branch_code = get_branch_code($id);
			if ($_REQUEST['v'])
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Branch activate ID#'.$id.' Branch Code: '.$branch_code);
			else
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Branch deactivate ID#'.$id.' Branch Code: '.$branch_code);
			load_table();
			//print "<script>alert('$LANG[MSTBRANCH_DATA_UPDATED]');</script>\n";
			exit;
			
		case 'u':
			$form = $_REQUEST;
			$errmsg = validate_data($form);

			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				$con->sql_query("select * from branch where id=$id");
				$original_form = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
			    $update_field = array('code', 'report_prefix', 'description', 'company_no', 'address', 'deliver_to', 'phone_1', 'phone_2', 'phone_3', 'contact_email', 'contact_person', 'con_dept_name', 'con_terms', 'con_lost_ci_discount', 'ci_allow_edit_selling_price', 'con_sort_by', 'con_split_artno', 'con_group_category');
			    if($sessioninfo['id']==1) $update_field[] = 'counter_limit';
			    if($config['masterfile_branch_region']){
					$update_field[] = 'region';
					if($id==1){	// update HQ
						$form['region'] = '';	// HQ cannot have region
					}
				}
				if($got_use_transporter)	$update_field[] = "transporter_id";
				
				// store basic info
				$con->sql_query("update branch set ".mysql_update_by_field($form, $update_field)." where id = $id");
				
				if ($con->sql_affectedrows()>0)
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

					log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Branch update information ' . $form['code'] . $changes);
					
					// change selling price base on region
					if($config['masterfile_branch_region'] && $config['sku_use_region_price'] && $original_form['region'] != $form['region'] && $form['region'] != ''){
						branch_change_sku_region_selling($id);
					}
					
					load_table();
					// saved. back to front page
					print "<script>parent.window.hidediv('ndiv');\nalert('$LANG[MSTBRANCH_DATA_UPDATED]');</script>";
				}
				else
					print "<script>parent.window.hidediv('ndiv');alert('$LANG[NO_CHANGES_MADE]');</script>";
			}
			exit;*/
		case 'load_trade_discount':
		    load_trade_discount();
		    exit;
		case 'save_discount_table':
		    save_discount_table();
		    exit;
		case 'sort_sequence':
		    sort_sequence();
		    exit;
		case 'save_branch_sequence':
		    save_branch_sequence();
		    exit;
		case 'print_branch_discount_table':
		    print_branch_discount_table();
		    exit;
		case 'print_envelope':
		    print_envelope();
		    exit;
		case 'convert_old_region':
			convert_old_region();
			exit;
		case 'print_branch_sequence':
			print_branch_sequence();
			exit;
		case 'ajax_add_branch':
			init_table();
			ajax_add_branch();
			exit;
		case 'ajax_edit_branch':
			init_table();
			ajax_edit_branch();
			exit;
		case 'ajax_update_branch':
			ajax_update_branch();
			exit;
		case 'load_table':
			load_table();
			exit;
		case 'ajax_activate':
			ajax_activate();
			exit;
		case 'update_branch_extra_info_structure':
			update_branch_extra_info_structure();
			exit;
		case 'branch_copy_settings':
			branch_copy_settings();
			exit;
		case 'show_gst_interbranch':
			show_gst_interbranch();
			exit;
		case 'save_gst_interbranch':
			save_gst_interbranch();
			exit;
		case 'ajax_show_outlet_photo':
			ajax_show_outlet_photo();
			exit;
		case 'upload_outlet_photo':
			upload_outlet_photo();
			exit;
		case 'delete_outlet_photo':
			delete_outlet_photo();
			exit;
		default:
			print "<h3>Unhandled Request</h3>";
			print_r($_REQUEST);
			exit;
	}

}

$con->sql_query("select * from branch order by sequence, code");
$smarty->assign("branches", $con->sql_fetchrowset());
$smarty->display("masterfile_branch_index.tpl");

function load_table(){

	global $con, $smarty;

	$con->sql_query("select * from branch order by sequence,code");
	$smarty->assign("branches", $con->sql_fetchrowset());
	$smarty->display("masterfile_branch_table.tpl");
}

function validate_data(&$form){
	global $LANG, $con, $id, $config;

	$errm = array();
	if ($form['code'] == '')	$errm[] = $LANG['MSTBRANCH_CODE_EMPTY'];
		
	// if old code != new code, check new code exists
	$con->sql_query("select * from branch where id <> $id and code = " . ms($form['code']));
	if ($con->sql_numrows() > 0)
	{
		$errm[] = sprintf($LANG['MSTBRANCH_CODE_DUPLICATE'], $form['code']);
	}

	// integration code		
	if ($form['integration_code'] != ''){
		$con->sql_query("select * from branch where id <> $id and integration_code = " . ms($form['integration_code']));
		if ($con->sql_numrows() > 0)
		{
			$errm[] = sprintf($LANG['MSTBRANCH_INTEGRATION_CODE_DUPLICATE'], $form['integration_code']);
		}
	}

	if ($form['address'] == '')
		$errm[] = $LANG['MSTBRANCH_ADDRESS_EMPTY'];
	if ($form['description'] == '')
		$errm[] = $LANG['MSTBRANCH_DESCRIPTION_EMPTY'];
	if ($form['company_no'] == '')
		$errm[] = $LANG['MSTBRANCH_COMPANY_NO_EMPTY'];
	/*if ($form['contact_email'] == '')
		$errm[] = $LANG['MSTBRANCH_EMAIL_EMPTY'];*/
	if ($form['contact_person'] == '')
		$errm[] = $LANG['MSTBRANCH_CONTACT_PERSON_EMPTY'];
	if ($form['phone_1'] == '')
		$errm[] = $LANG['MSTBRANCH_PHONE_1_EMPTY'];
	if ($form['contact_email'] && !preg_match(EMAIL_REGEX, $form['contact_email']))
		$errm[] = $LANG['MSTBRANCH_INVALID_EMAIL'];
	if ($form['use_branch_logo']){
		$file = $_FILES['logo'];
		if(!file_exists($f = 'ui/branch_logo-'.$id.'.png') && !is_readable($file['tmp_name'])){ // check file existence
			$errm[] = $LANG['BRANCH_LOGO_EMPTY'];
		}elseif($file['tmp_name']){ // check file extension
			$valids = array('jpg','jpeg');
			$ext = pathinfo($file['name'],PATHINFO_EXTENSION);
			if (!in_array($ext,$valids)) $errm[] = $LANG['BRANCH_LOGO_INV_FORMAT'];
		}
		
		if($file['size'] > 5242880){ // check logo file size (5mb)
			$errm[] = $LANG['MSTBRANCH_LOGO_SIZE_EXCEEDED'];
		}
	}
	if($config['consignment_modules']){
	    $form['con_dept_name'] = ucwords($form['con_dept_name']);
	    $form['con_terms'] = ucwords($form['con_terms']);
	    
		if($form['con_dept_name']=='')  $errm[] = $LANG['MSTBRANCH_DEPT_NO_EMPTY'];
		if($form['con_terms']=='')  $errm[] = $LANG['MSTBRANCH_TERMS_NO_EMPTY'];
	}
	if(($form['gst_register_no'] && $form['gst_start_date'] == 0) || (!$form['gst_register_no'] && $form['gst_start_date'] > 0))
		$errm[] = $LANG['MSTBRANCH_GST_INVALID'];
	
	return $errm;
}

function init_table(){
	global $config, $con, $smarty, $got_use_transporter;
	
	if($got_use_transporter){
		$con->sql_query("select * from consignment_transporter order by code, company_name");
		$transporters = array();
		while($r = $con->sql_fetchassoc()){
			$transporters[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('transporters', $transporters);
	}
}

function load_trade_discount(){
	global $con,$smarty;
	$branch_id = intval($_REQUEST['branch_id']);
	
	//branch_info
	$con->sql_query("select * from branch where id=$branch_id") or die(mysql_error());
	$branch_info = $con->sql_fetchrow();
	
	// trade discount type and info
	$con->sql_query("select tdt.*,bt.value from trade_discount_type tdt
left join branch_trade_discount bt on tdt.id=bt.trade_discount_id and bt.branch_id=$branch_id") or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$trade_info[$r['id']] = $r;
	}
	
	$smarty->assign('trade_info',$trade_info);
	$smarty->assign('branch_info',$branch_info);
	$smarty->display('masterfile_branch.trade_discount_table.tpl');
}

function save_discount_table(){
	global $con,$smarty,$sessioninfo;
	
	$branch_id = intval($_REQUEST['branch_id']);
	$trade_discount = $_REQUEST['trade_discount'];
	
	if($trade_discount){
		foreach($trade_discount as $tid=>$value){
			$upd = array();
			$upd['branch_id'] = $branch_id;
			$upd['trade_discount_id'] = $tid;
			$upd['value'] = $value;
			$con->sql_query("replace into branch_trade_discount ".mysql_insert_by_field($upd)) or die(mysql_error());
		}
		$branch_code = get_branch_code($branch_id);
		log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Change Trade Discount Branch ID#'.$branch_id.' Branch Code: '.$branch_code);
		print "OK";
	}else{
		print "Error: No Trade Discount Found.";
	}
}

function sort_sequence(){
    global $con,$smarty,$sessioninfo;
    
    if($sessioninfo['level']<5000)  die("You can't access this page.");

	$sort_by = 'sequence';
	$order = 'asc';

	if($_REQUEST['sort_by']!=''){
        $sort_by = $_REQUEST['sort_by'];
        if (!$_REQUEST['print']){
	        if($_SESSION['branch_sort'][$sort_by]=='desc'||$_SESSION['branch_sort'][$sort_by]==''){
	            $order = 'asc';
	            $_SESSION['branch_sort'][$sort_by] = 'asc';
			}else{
	            $order = 'desc';
	            $_SESSION['branch_sort'][$sort_by] = 'desc';
			}
		}else{
			$order=$_SESSION['branch_sort'][$sort_by];
		}
	}
	
    $con->sql_query("select b.* from branch b order by $sort_by $order");
    while($r = $con->sql_fetchrow()){
		$branches[$r['id']] = $r;
	}
    
    $smarty->assign('branches',$branches);
	$smarty->display('masterfile_branch.sort_sequence.tpl');
}

function save_branch_sequence(){
    global $con,$smarty,$sessioninfo;
    
    if($sessioninfo['level']<5000)  die("You can't access this page.");
    
    $bid_list = explode(",",$_REQUEST['bid_list']);
    
    if($bid_list){
        $index=0;
		foreach($bid_list as $bid){
		    $index++;
			$con->sql_query("update branch set sequence=".mi($index)." where id=".mi($bid)) or die(mysql_error());
		}
	}
	
	print "OK";
}

function print_branch_discount_table(){
	global $con, $smarty, $config;
	if(!$config['masterfile_branch_allow_print_discount_table'])    die("You can't access this page.");
	
	$con->sql_query("select count(*) from branch where active=1");
	$total_rows = $con->sql_fetchfield(0);
	
	$con->sql_query("select * from branch where active=1 order by sequence, code");
	$branches = $con->sql_fetchrowset();
	
	$con->sql_query("select * from trade_discount_type order by code");
	$smarty->assign("trade_discount_type", $con->sql_fetchrowset());
	
	$item_per_page = $config['masterfile_branch_print_discount_table_item_per_page'] ? $config['masterfile_branch_print_discount_table_item_per_page'] : 30;
	$totalpage = ceil($total_rows/$item_per_page);
	
	$con->sql_query("select * from branch_trade_discount");
	while($r = $con->sql_fetchrow()){
		$data[$r['branch_id']][$r['trade_discount_id']] = $r['value'];
	}
	$smarty->assign('data', $data);
	
	$start_counter = 1;
	$page = 1;
	$smarty->assign('totalpage', $totalpage);
	$smarty->assign('PAGE_SIZE',$item_per_page);

    include_once("include/excelwriter.php");
	$smarty->assign('no_header_footer', true);
	$smarty->assign('open_mode','view');
	$filename = "discount_table_".time().".xls";
	log_br($sessioninfo['id'], 'DISCOUNT_TABLE_EXPORT', 0, "Export Branch Discount Table To Excel($filename)");
	Header('Content-Type: application/msexcel');
	Header('Content-Disposition: attachment;filename='.$filename);

	print ExcelWriter::GetHeader();
    for($i=0; $i<$totalpage; $i++){
        $smarty->assign('start_counter', $start_counter);
	    $smarty->assign('page', $page);
	    $smarty->assign('branches', array_slice($branches, $i*$item_per_page, $item_per_page));
	    $smarty->display('masterfile_branch.print_branch_discount_table.tpl');
	    $smarty->assign("skip_header",1);
	    $page++;
    }
	print ExcelWriter::GetFooter();
    exit;
}

function print_envelope(){
	global $con, $smarty, $config;
	if(!$config['masterfile_branch_allow_print_envelope'])    die("You can't access this page.");
	
	$bid = mi($_REQUEST['selected_bid']);
	$con->sql_query("select * from branch where id=$bid");
	$to_branch = $con->sql_fetchrow();
	if(!$to_branch) print "<script>alert('Invalid Branch');</script>";
	$smarty->assign('to_branch', $to_branch);
	if($config['masterfile_branch_envelope_alt_print_template'])    $smarty->display($config['masterfile_branch_envelope_alt_print_template']);
	else	$smarty->display('masterfile_branch.print_envelope.tpl');
}

function convert_old_region(){
	global $con, $smarty, $config;
	
	if(!$config['masterfile_branch_region'])	die('Config Not Set');
	$region_keys = array();
	$region_to_key = array();
	foreach($config['masterfile_branch_region'] as $k=>$cf){
		if(is_numeric($k) || !is_array($cf) || trim($cf['name'])=='')	die('Config Wrong Structure');
		$region_keys[] = ms($k);
		
		$region_to_key[trim($cf['name'])] = $k;
	}
	
	$sql = "select id,region from branch where (region<>'' and region is not null) and region not in (".join(',', $region_keys).")";
	//print $sql;
	$q1 = $con->sql_query($sql);
	$update_count = 0;
	while($r = $con->sql_fetchassoc($q1)){
		$region = trim($r['region']);
		if(!$region)	continue;
		
		$new_region = $region_to_key[$region];
		$con->sql_query("update branch set region=".ms($new_region)." where id=".mi($r['id']));
		$update_count++;
	}
	$con->sql_freeresult($q1);
	
	print "$update_count branch(s) updated.";
}

function print_branch_sequence(){
	global $con,$smarty,$sessioninfo;
    
    if($sessioninfo['level']<5000)  die("You can't access this page.");

	$sel_branch = $_REQUEST['sel_branch'];
	$skip_inactive = mi($_REQUEST['skip_inactive']);
	if(!$sel_branch || !is_array($sel_branch))	die('No Branch avaiable to print');
	
	$filter = array();
	if($skip_inactive)	$filter[] = "b.active=1";
	if($filter)	$filter = "where ".join(' and ', $filter);
	else	$filter = '';
	
	$tmp_branches = array();
    $con->sql_query("select b.* from branch b $filter");
    while($r = $con->sql_fetchrow()){
		$tmp_branches[$r['id']] = $r;
	}
	$con->sql_freeresult();
    
    $branches = array();
    foreach($sel_branch as $bid){
		$bid = mi($bid);
		$b = $tmp_branches[$bid];
		
		if(!$b)	continue;	// no this branch
		if($skip_inactive && !$b['active'])	continue;	// inactive branch
		
		$branches[$bid] = $b;
	}
    $smarty->assign('branches',$branches);
    $smarty->display('masterfile_branch.print_branches.tpl');
}

function ajax_add_branch(){
	global $is_add_branch;
	
	$is_add_branch = true;
	ajax_edit_branch();
}

function ajax_edit_branch(){
	global $con, $config, $sessioninfo, $is_add_branch, $allow_add_branch, $smarty;
	
	$bid = mi($_REQUEST['bid']);
	
	if($is_add_branch){
		if(!$allow_add_branch){
			die('Invalid Action.');
		}
	}else{
		if(!$bid){
			die('Invalid Branch.');
		}
	}
	
	if($bid){
		$con->sql_query("select * from branch where id = $bid");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();

		if (file_exists($f = 'ui/branch_logo-'.$bid.'.png')) $form['logo'] = $f;
		
		if ($config['branch_extra_info']) {
			$con->sql_query("select * from branch_extra_info where branch_id = $bid");
			$form_extra = $con->sql_fetchassoc();
			$con->sql_freeresult();
		}
		
		if (!$form){
			die("Invalid branch ID: $bid");
		}
	}
	
	
	if($config['masterfile_use_branch_type']){
		$con->sql_query("select * from debtor order by code, description");
		$debtor_list = $con->sql_fetchrowset();
		$con->sql_freeresult();
		$smarty->assign("debtors", $debtor_list);
	}
	
	$timezone = generate_timezone_list();
	$smarty->assign("timezone", $timezone);
	
	if(!$form["timezone"]){
		if(in_array(date_default_timezone_get(), $timezone)){
			$form["timezone"] = date_default_timezone_get();
		}else{
			$form["timezone"] = "Asia/Kuala_Lumpur";
		}
	}
	
	$con->sql_query("select id,code from branch where active=1 order by sequence, code");
	$brc = $con->sql_fetchrowset();
	$con->sql_freeresult();
	
	$smarty->assign("branches", $brc);
	
	$smarty->assign('form', $form);
	$smarty->assign('form_extra', $form_extra);
	$smarty->display('masterfile_branch.open.tpl');
}

function generate_timezone_list()
{
    static $regions = array(
        DateTimeZone::AFRICA,
        DateTimeZone::AMERICA,
        DateTimeZone::ANTARCTICA,
        DateTimeZone::ASIA,
        DateTimeZone::ATLANTIC,
        DateTimeZone::AUSTRALIA,
        DateTimeZone::EUROPE,
        DateTimeZone::INDIAN,
        DateTimeZone::PACIFIC,
    );

    $timezones = array();
    foreach( $regions as $region )
    {
        $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
    }

    $timezone_offsets = array();
    foreach( $timezones as $timezone )
    {
        $tz = new DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
    }

    ksort($timezone_offsets);

    $timezone_list = array();
    foreach( $timezone_offsets as $timezone => $offset )
    {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate( 'H:i', abs($offset) );

        $pretty_offset = "UTC${offset_prefix}${offset_formatted}";
		$timezone_str = str_replace("_", " ", $timezone);
        $timezone_list[$timezone] = "(${pretty_offset}) $timezone_str";
    }

    return $timezone_list;
}

function ajax_update_branch(){
	global $con, $config, $sessioninfo, $allow_add_branch, $smarty, $got_use_transporter;
	
	$id = mi($_REQUEST['id']);
	
	$form = $_REQUEST;
	
	$upd = array();
	$upd['code'] = $form['code'];
	$upd['report_prefix'] = $form['report_prefix'];
	$upd['description'] = $form['description'];
	$upd['company_no'] = $form['company_no'];
	$upd['address'] = $form['address'];
	$upd['deliver_to'] = $form['deliver_to'];
	$upd['phone_1'] = $form['phone_1'];
	$upd['phone_2'] = $form['phone_2'];
	$upd['phone_3'] = $form['phone_3'];
	$upd['contact_email'] = $form['contact_email'];
	$upd['contact_person'] = $form['contact_person'];
	$upd['con_dept_name'] = $form['con_dept_name'];
	$upd['con_terms'] = $form['con_terms'];
	$upd['con_lost_ci_discount'] = $form['con_lost_ci_discount'];
	$upd['ci_allow_edit_selling_price'] = $form['ci_allow_edit_selling_price'];
	$upd['con_sort_by'] = $form['con_sort_by'];
	$upd['con_split_artno'] = $form['con_split_artno'];
	$upd['con_group_category'] = $form['con_group_category'];
	$upd['inactive_change_price'] = mi($form['inactive_change_price']);
	$upd['trans_end_date'] = $form['trans_end_date'];
	$upd['type'] = $form['type'];
	$upd['is_vertical_logo'] = $form['is_vertical_logo'];
	$upd['vertical_logo_no_company_name'] = $form['vertical_logo_no_company_name'];
	$upd['operation_time'] = $form['operation_time'];
	$upd['longitude'] = $form['longitude'];
	$upd['latitude'] = $form['latitude'];
	$upd['warehouse_number'] = $form['warehouse_number'];
	$upd['warehouse_name'] = $form['warehouse_name'];
	if($config['enable_reorder_integration'])	$upd['integration_code'] = $form['integration_code'];
	$upd['debtor_id'] = $form['debtor_id'];
	$upd['timezone'] = $form['timezone'];
	if($config['enable_gst']){
		$upd['gst_register_no'] = $form['gst_register_no'];
		$upd['gst_start_date'] = $form['gst_start_date'];
		$upd['is_export'] = $form['is_export'];

        $upd['account_code'] = $form['account_code'];
        $upd['account_code_debtor'] = $form['account_code_debtor'];
        $upd['account_receivable_code'] = $form['account_receivable_code'];
        $upd['account_receivable_name'] = $form['account_receivable_name'];
        $upd['account_payable_code'] = $form['account_payable_code'];
        $upd['account_payable_name'] = $form['account_payable_name'];
	}
	$upd['use_branch_logo'] = $form['use_branch_logo'];
	
	if ($config['branch_extra_info']) {
		foreach ($config['branch_extra_info'] as $bkey=>$bvalue) {
			$upd_extra[$bkey] = $form[$bkey];
		}
	}
	
	if($sessioninfo['id']==1)    $upd['counter_limit'] = $form['counter_limit'];
	if($config['masterfile_branch_region']) $upd['region'] = $form['region'];
	if($got_use_transporter)	$upd['transporter_id'] = $form["transporter_id"];
	
	$err = validate_data($upd);
	
	if(!$id && !$allow_add_branch){
		$err[] = "Invalid Branch ID";
	}
	
	if($err){
		/*
		foreach($err as $e){
			print "$e\n";
		}
		*/
		iframe_return($err[0]);
		exit;
	}
	
	if($id){	// update branch
		$con->sql_query("select * from branch where id=$id");
		$original_form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$edited_fields = array();
		foreach($original_form as $col=>$old_value){
			if(isset($upd[$col]) && $upd[$col] != $old_value){
				$edited_fields[] = $col;
			}
		}
		unset($upd['use_branch_logo']);
		$con->sql_query("update branch set ".mysql_update_by_field($upd)." where id=$id");
		
		if ($_REQUEST['use_branch_logo'])
		{
			$file = $_FILES['logo']['tmp_name'];
			if ($file) save_branch_logo($id);
		}
		else
		{
			//remove the file, if previously exists
			if (file_exists($f = 'ui/branch_logo-'.$id.'.png')) @unlink($f);
		}
		
		if ($config['branch_extra_info']) {
			$upd_extra['added']='CURRENT_TIMESTAMP';
			$upd_extra['last_update']='CURRENT_TIMESTAMP';
			$upd_extra['branch_id']=$id;
			$con->sql_query("replace into branch_extra_info set " . mysql_update_by_field($upd_extra));
		}
		
		$changes .= "\nEdited fields: (" . join(", ", $edited_fields) . ")";
		log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Branch update information ' . $upd['code']." ".$changes);
					
		// change selling price base on region
		if($config['masterfile_branch_region'] && $config['sku_use_region_price'] && $original_form['region'] != $upd['region'] && $upd['region'] != '' && $form['force_update_rprice']){
			$prms = array();
			$prms['branch_id'] = $id;
			$prms['old_region'] = $original_form['region'];
			branch_change_sku_region_selling($prms);
		}
	}else{	// add new branch
		unset($upd['use_branch_logo']);
		$upd['added'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("insert into branch " . mysql_insert_by_field($upd));
		$new_bid = $con->sql_nextid();  // get the new branch id
		
		if ($_REQUEST['use_branch_logo']) save_branch_logo($new_bid);
		
		if ($config['branch_extra_info']) {
			$upd_extra['added']='CURRENT_TIMESTAMP';
			$upd_extra['branch_id']=$new_bid;
			$con->sql_query("replace into branch_extra_info set " . mysql_update_by_field($upd_extra));
		}
		
		// create branch status row
		$branch_status = array();
		$branch_status['branch_id'] = $new_bid;
		$branch_status['lastping'] = '';
		$con->sql_query("insert into branch_status ".mysql_insert_by_field($branch_status));
		
		log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Branch create ' . $form['code']);  // record log
	
		update_sales_cache($new_bid, -1);    // create all cache table
		
		//copy settings from a branch
		/*
		*/
		/*if ($form['copy_selling_price']) copy_selling_price_by_branch($form['copy_selling_price'],$new_bid); //copy selling price
		if ($form['copy_block_po']) copy_block_po_by_branch($form['copy_block_po'],$new_bid); //copy block po
		if ($form['copy_pos_settings']) copy_pos_settings_by_branch($form['copy_pos_settings'],$new_bid); //copy pos settings
		if ($form['copy_discount']) copy_discount_by_branch($form['copy_discount'],$new_bid); //copy discount
		if ($form['copy_point']) copy_point_by_branch($form['copy_point'],$new_bid); //copy discount
		
		// change selling price base on region
		if($config['masterfile_branch_region'] && $config['sku_use_region_price'] && $upd['region'] != ''){
			branch_change_sku_region_selling($new_bid);
		}*/
	}
	
	//print "OK";
	iframe_return('OK');
}

function ajax_activate(){
	global $con, $sessioninfo;
	
	$bid = mi($_REQUEST['bid']);
	$active = mi($_REQUEST['v']);
	
	$con->sql_query("update branch set active = $active where id = $bid");
	$branch_code = get_branch_code($bid);
	if ($active)
		log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Branch activate ID#'.$bid.' Branch Code: '.$branch_code);
	else
		log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Branch deactivate ID#'.$bid.' Branch Code: '.$branch_code);
}

function update_branch_extra_info_structure(){
	global $con, $config;
	
	if(!$config['branch_extra_info'])	die("Config Not Found");
	
	$con->sql_query("explain branch_extra_info");
	$c_info = array();
	while($r = $con->sql_fetchassoc()){
		$c_info[$r['Field']] = $r;
	}
	$con->sql_freeresult();
	
	$alter_query = array();
	foreach($config['branch_extra_info'] as $c => $r){
		$data_type = trim($r['data_type']);
		if(isset($r['default_value']))	$default_value = $r['default_value'];
		
		if(!$data_type)	die("Invalid Datatype for $c");
		
		if(!isset($c_info[$c]))	$alter_query[] = "add $c $data_type ".(isset($default_value) ? "Default ".ms($default_value) : "");	// need add column
		else{
			if($c_info[$c]['Type'] != $r['data_type'] || ($c_info[$c]['Default'] != $default_value || isset($c_info[$c]['Default']) != isset($default_value))){	// need modify
				$alter_query[] = "modify $c $data_type ".(isset($default_value) ? "Default ".ms($default_value) : "");
			}
		}
		unset($default_value);
	}
	if($alter_query){
		$str = "alter table branch_extra_info ".join(',', $alter_query);
		print "$str<br>";
		$con->sql_query($str);
	}
	print "Done.";
}

function branch_copy_settings(){
	global $con, $config, $allow_add_branch;

	$form = $_REQUEST;
	
	if(!$allow_add_branch) die('Invalid Action.');
	
	if(!$form['cbid']) die("No Branch selected.");
	elseif(!$form['copy_from_bid']) die("No Branch copy selected");
	
	// load current branch info
	$con->sql_query("select * from branch where id = ".mi($form['cbid']));
	$b_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	// prevent client abort
	ignore_user_abort(true);
	set_time_limit(0);
		
	if ($form['copy_type'] == 1) copy_selling_price_by_branch($form['copy_from_bid'], $form['cbid']); //copy selling price
	elseif ($form['copy_type'] == 2) copy_block_po_by_branch($form['copy_from_bid'], $form['cbid']); //copy block po
	elseif ($form['copy_type'] == 3) copy_pos_settings_by_branch($form['copy_from_bid'], $form['cbid']); //copy pos settings
	elseif ($form['copy_type'] == 4) copy_discount_by_branch($form['copy_from_bid'], $form['cbid']); //copy discount
	elseif ($form['copy_type'] == 5) copy_point_by_branch($form['copy_from_bid'], $form['cbid']); //copy points
	elseif ($form['copy_type'] == 6) copy_trade_discount_by_branch($form['copy_from_bid'], $form['cbid']); //copy trade discount
	elseif ($form['copy_type'] == 7) copy_approval_flow_by_branch($form['copy_from_bid'], $form['cbid']); //copy approval flow
	elseif ($form['copy_type'] == 8) copy_block_grn_by_branch($form['copy_from_bid'], $form['cbid']); //copy block grn
	else die("No Copy Type selected");
	
	// change selling price base on region
	if($config['masterfile_branch_region'] && $config['sku_use_region_price'] && $b_info['region'] != ''){
		$prms = array();
		$prms['branch_id'] = $form['cbid'];
		branch_change_sku_region_selling($prms);
	}
	
	$ret = array();
	$ret['ok'] = 1;
	
	print json_encode($ret);
}

function iframe_return($msg)
{
	print '<script type="text/javascript">';
	if ($msg == 'OK') print 'parent.iframe_callback(true);';
	else print 'parent.iframe_callback("'.$msg.'");';
	print '</script>';
	/*
	print '<pre>';
	print_r($_REQUEST);
	print_r($_FILES);
	print '</pre>';
	*/
}

function save_branch_logo($bid)
{
	$file = $_FILES['logo'];
	
	if (is_readable($file['tmp_name']))
	{
		$valids = array('jpg','jpeg');
		$ext = pathinfo($file['name'],PATHINFO_EXTENSION);
		
		if (!in_array($ext,$valids)){
			$result = 'Error while loading logo image';
			return $result;
		}
		
		$tmp_img_location = "/tmp/branch_logo-".$bid.".png";
		$img_location = "ui/branch_logo-".$bid.".png";
		copy($file['tmp_name'], $tmp_img_location);
		if(file_exists($tmp_img_location)) resize_photo($tmp_img_location, $img_location);
	}
	else {
		$result = 'Error uploading logo image';
	}
	
	return $result;
}

function show_gst_interbranch(){
	global $con, $smarty, $config;
	
	$gst_interbranch = array();
	$q1 = $con->sql_query("select * from gst_interbranch");
	while($r = $con->sql_fetchassoc($q1)){
		$gst_interbranch[$r['branch_id_1']][$r['branch_id_2']] = $r;
		$gst_interbranch[$r['branch_id_2']][$r['branch_id_1']] = $r;
	}
	$con->sql_freeresult($q1);
	
	$branches = array();
	$qb = $con->sql_query("select * from branch order by sequence, code");
	while($r = $con->sql_fetchassoc($qb)){
		$branches[$r['id']] = $r;
	}
	$con->sql_freeresult($qb);
	
	$smarty->assign('branches', $branches);
	$smarty->assign('gst_interbranch', $gst_interbranch);
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['html'] = $smarty->fetch('masterfile_branch.gst_interbranch.tpl');
	
	print json_encode($ret);
}

function save_gst_interbranch(){
	global $con, $sessioninfo;
	
	$gst_interbranch = $_REQUEST['gst_interbranch'];
	//print_r($gst_interbranch);
	
	$con->sql_query("truncate gst_interbranch");
	if($gst_interbranch){
		foreach($gst_interbranch as $bid => $b2_list){
			foreach($b2_list as $bid2 => $v){
				$upd = array();
				$upd['branch_id_1'] = $bid;
				$upd['branch_id_2'] = $bid2;
				//print_r($upd);
				$con->sql_query("replace into gst_interbranch ".mysql_insert_by_field($upd));
			}
		}
	}
	log_br($sessioninfo['id'], 'MASTERFILE', 0, 'GST Interbranch update, data =>'.print_r($gst_interbranch, true));
	
	$ret = array();
	$ret['ok'] = 1;
		
	print json_encode($ret);
}

function ajax_show_outlet_photo(){
	global $con, $smarty;

	$branch_id = mi($_REQUEST['branch_id']);
	if(!$branch_id) die("Invalid Branch ID");
	
	$q1 = $con->sql_query("select * from branch where id = $branch_id");
	$r1 = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	$form['outlet_photo_url'] = $r1['outlet_photo_url'];
	$form['branch_id'] = $branch_id;
	$smarty->assign('form', $form);
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['html'] = $smarty->fetch('masterfile_branch.show_photo.tpl');
	print json_encode($ret);
}

function upload_outlet_photo(){
	global $con, $smarty, $sessioninfo, $appCore; 
	$branch_id = mi($_REQUEST['branch_id']);
	
	if(!$branch_id){
		print "<script>parent.window.alert('Invalid Branch ID.');parent.window.OUTLET_PHOTO_DIALOG.photo_uploaded_failed();</script>";
		exit;
	}
	
	// Create Folder
	$folder = "attch/outlet_photo";
	if(!file_exists($folder)){
		$success = check_and_create_dir($folder);
		if(!$success){
			print "<script>parent.window.alert('Unable to Create Branch Outlet Folder');parent.window.OUTLET_PHOTO_DIALOG.photo_uploaded_failed();</script>";
			exit;
		}
	}
	
	$fname = 'outlet_photo';
	// Check File
	$result = $appCore->isValidUploadImageFile($_FILES[$fname]);
	if(!$result['ok']){
		print "<script>parent.alert(".jsstring($result['error']).");parent.window.OUTLET_PHOTO_DIALOG.photo_uploaded_failed();</script>";
		exit;
	}
	
	$ext = trim($result['ext']);
	if(!$ext){
		print "<script>parent.alert('Invalid File Extension');parent.window.OUTLET_PHOTO_DIALOG.photo_uploaded_failed();</script>";
		exit;
	}
	
	$final_path = $folder."/".$branch_id.".jpg";
	
	// Move File to Actual Folder
	if(move_uploaded_file($_FILES[$fname]['tmp_name'], $final_path)){
		$file_uploaded = true;
	}else{
		$file_uploaded = false;
	}
	
	// Call Back
	if($file_uploaded){
		$con->sql_query("update branch set outlet_photo_url= ".ms($final_path)."  where id=$branch_id");
		log_br($sessioninfo['id'], 'MASTERFILE', $branch_id, "Branch Outlet Photo Updated: ID#".$branch_id);
		print "<script>parent.window.OUTLET_PHOTO_DIALOG.photo_uploaded('$final_path?".time()."');</script>";
	}else{
		print "<script>parent.alert('Fail to overwrite image file');parent.window.OUTLET_PHOTO_DIALOG.photo_uploaded_failed();</script>";
	}
}

function delete_outlet_photo(){
	global $con, $smarty, $sessioninfo;

	$ret = array();
	$branch_id = mi($_REQUEST['branch_id']);
	if(!$branch_id) die("Invalid Branch ID");
	
	$q1 = $con->sql_query("select outlet_photo_url from branch where id = $branch_id");
	$r1 = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	$file_path = $r1['outlet_photo_url'];
	
	$con->sql_query("update branch set outlet_photo_url='' where id=$branch_id");
	log_br($sessioninfo['id'], 'MASTERFILE', $branch_id, "Delete Branch Outlet Photo: ID#".$branch_id);
	if(file_exists($file_path)){
		unlink($file_path);
	}
	$ret['ok'] = 1;
	print json_encode($ret);
}
?>
