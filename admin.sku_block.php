<?php
/*
12/10/2010 10:11:38 AM Andy
- Add a link to view sample csv and put a legend to let user know what field is accepted in csv.
- Make it able to accept mcode

6/24/2011 3:11:36 PM Andy
- Make all branch default sort by sequence, code.

1/9/2012 5:39:43 PM Justin
- Renamed the name "Block / Unblock SKU (CSV)" into "Block / Unblock SKU in PO (CSV)".
- Fixed bugs that overwrite previous blocked/unblocked list from sku item while user do blocking/unblocking.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level']<$user_level['MIS Assistant'] || BRANCH_CODE != 'HQ') js_redirect('Access Denied', "/index.php");

set_time_limit(0);
ini_set("memory_limit", "64M");
ini_set("display_errors",1);

if(isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	    case 'view_sample':
	        print "280000010000<br />280000020000<br />280000030000";
	        exit;
		default:
		    print "Unhanlde Error!";
		    exit;
	}
}

if (!$_FILES['f'] || $_FILES['f']['error']){    // nothing is submit or submmit error
	show_form();
	exit;
}

$bid = intval($_REQUEST['branch_id']);
$bcode = get_branch_code($bid);
// check and perform sql-insertion
$f = $_FILES['f'];

$fp = fopen($f['tmp_name'], "r");
$msg = '';
$count = 0;
while($line = fgetcsv($fp)){
    //if (!preg_match('/^28/', $line[0])) continue;
    
	$code = trim($line[0]); // only get the first column data
	
	if(!$code)  continue;   // skip if empty
	
	// retrieve sku
	$sku_query = "select id, block_list from sku_items where sku_item_code=".ms($code)." or mcode=".ms($code);
	//print $sku_query."<br />";
	$con->sql_query($sku_query);
	$item = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	// cannot retrive this code, it is invalid sku
	if(!$item){
        $msg .= "<li style='color:red;'> $code is not a valid SKU.</li>";
		continue;
	}
	$sid = mi($item['id']);
	$upd = array();
	
	if ($_REQUEST['action'] == 'Block' || $_REQUEST['action'] == 'Unblock') // is block/unblock
	{
		$b = unserialize($item['block_list']); // get current block list
		
		if (!is_array($b)) $b = array();    // escape array to avoid warning
		
		if ($_REQUEST['branch_id'] == 'All')    // all branches selected
		{
		    if(!$branches){ // select all branches
                $con->sql_query("select id from branch");
				$branches = $con->sql_fetchrowset();
				$con->sql_freeresult();
			}
			
			foreach($branches as $br)
			{
				if ($_REQUEST['action'] == 'Block')
					$b[$br['id']] = "on";
				else
					unset($b[$br['id']]);
			}
		}
		else    // single branch selected
		{
			if ($_REQUEST['action'] == 'Block')
				$b[$bid] = "on";
			else
				unset($b[$bid]);
		}	
		$upd['block_list'] = serialize($b);
		
	}
	elseif ($_REQUEST['action'] == 'Active' || $_REQUEST['action'] == 'Inactive')
	{
		
		if ($_REQUEST['action'] == 'Active') 
			$value = 1;
		else
			$value = 0;

		$upd['active'] = $value;
		log_br($sessioninfo['id'], 'MASTERFILE_SKU_ACT', $sid, $code);
	}
	
	// update sku items
	$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id=$sid");
	$count++;
}

if ($count==0)
{
	$msg = "<li> <font color=red>Please make sure you have uploaded a valid CSV file.</li></font>";
}
else
{		

	if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments"))
	mkdir($_SERVER['DOCUMENT_ROOT']."/attachments",0777);
	
	if ($_REQUEST['action'] == 'Block' || $_REQUEST['action'] == 'Unblock')
	{
		if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments/block_sku_history"))
			mkdir($_SERVER['DOCUMENT_ROOT']."/attachments/block_sku_history",0777);

		$history_file = time().".".$f['name'];
		move_uploaded_file($f['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/attachments/block_sku_history/$history_file");
		log_br($sessioninfo['id'], 'SKU', '', $_REQUEST['action']." SKU using $history_file, Branch $bcode, $count items.");
	
		if ($_REQUEST['action'] == 'Block')
			$msg .= "<li> <font color=blue>Branch: $bcode. $count sku blocked.</font>";
		else
			$msg .= "<li> <font color=blue>Branch: $bcode. $count sku unblocked.</font>";
		
		$msg .= "<li> File copied to /attachments/block_sku_history/$history_file";
	}
	elseif ($_REQUEST['action'] == 'Active' || $_REQUEST['action'] == 'Inactive')
	{
		if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments/active_sku_history"))
			mkdir($_SERVER['DOCUMENT_ROOT']."/attachments/active_sku_history",0777);

		$history_file = time().".".$f['name'];
		move_uploaded_file($f['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/attachments/active_sku_history/$history_file");
		log_br($sessioninfo['id'], 'SKU', '', $_REQUEST['action']." SKU using $history_file, $count items.");
	
		if ($_REQUEST['action'] == 'Active')
			$msg .= "<li> <font color=blue>$count sku is set to active.</font>";
		else
			$msg .= "<li> <font color=blue>$count sku is set to inactive.</font>";
		
		$msg .= "<li> File copied to /attachments/active_sku_history/$history_file";
	
	}
}

fclose($fp);
show_form($msg);
print "<pre>";
print $query_time;

function show_form($msg = '')
{
	global $smarty, $con, $sessioninfo;
	
	$smarty->assign("PAGE_TITLE", "Block / Unblock SKU in PO (CSV)");
	$smarty->display("header.tpl");
	
?>

<script>
function check_action(obj)
{
	if (obj.value == 'Active' || obj.value == 'Inactive')
	{
		document.getElementById('branch').style.display = 'none';
	}
	else
	{
		document.getElementById('branch').style.display = '';
	}
}
</script>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">Block / Unblock SKU in PO (CSV)</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div> 
<div class="container">
	<div class="card mx-3">
		<div class="card-body">
		<form enctype="multipart/form-data" method="post">

<?
	print "<ul>$msg</ul>";
?>

<label>Action </label>
<select name="action" class="form-control select2" onchange="check_action(this);">
<option <? if ($_REQUEST['action'] == 'Block') print "selected";?>>Block</option>
<option <? if ($_REQUEST['action'] == 'Unblock') print "selected";?>>Unblock</option>
<option <? if ($_REQUEST['action'] == 'Active') print "selected";?>>Active</option>
<option <? if ($_REQUEST['action'] == 'Inactive') print "selected";?>>Inactive</option>
</select>
<?
if (BRANCH_CODE != 'HQ')
{
    print "<input type=hidden name=branch_id value=$sessioninfo[branch_id]>";
}
else
{
	print "<span id=branch ";
	if ($_REQUEST['action'] == 'Active' || $_REQUEST['action'] == 'Inactive') print "style='display:none'";
	print " >&nbsp;&nbsp;&nbsp; Branch <select name=branch_id class=\"form-control select2\">";
	print "<option value='All' ";
	if ($_REQUEST['branch_id'] == 'All') print "selected";
	print ">All</option>";
	$con->sql_query("select code, id from branch order by sequence, code");
	while($r=$con->sql_fetchrow())
	{
	    print "<option value=$r[id] ";
		if ($r['id']==$_REQUEST['branch_id']) print "selected";
		print ">$r[code]</option>";
	}
	print "</select></span>";
}
?>
<br /><br />
Please select CSV list <input type=file name=f size=30>
<input type=submit class="btn btn-primary" value="Run">
&nbsp;&nbsp;<a href="?a=view_sample" target="_blank"> View sample</a> (Accept ARMS Code, MCode)
</form>
		</div>
	</div>
</div>
<?
	$smarty->display("footer.tpl");
	exit;
}

?>
