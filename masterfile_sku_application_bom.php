<?
/*
11/6/2019 3:59 PM William
- Enhanced to add sku type.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
$smarty->assign("PAGE_TITLE", "Create BOM SKU");

// if not HQ, connect to HQ
$hqcon = connect_hq();
init_selection();

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	    case 'save':
	    	$form=$_REQUEST;
			$errm = validate_data($form);
			if (!$errm){
				$form['is_bom']=1;
       			$form['apply_branch_id'] = $sessioninfo['branch_id'];
    			$form['added'] = date("Y-m-d H:i:s");
    			$form['vendor_id']=0;
    			
				$hqcon->sql_query("insert into sku " . mysql_insert_by_field($form, array("sku_type", "is_bom", "apply_by", "apply_branch_id", "category_id","brand_id","remark", "status","added", "vendor_id")));
				$sku_id = $hqcon->sql_nextid();
				
				$hqcon->sql_query("update sku set sku_code=28000000+id where id=$sku_id");
				header("Location: /bom.php?t=completed&sku_id=$sku_id");			
			}
			else{
		    	$smarty->assign("form", $form);
			    $smarty->assign("errm", $errm);
			}
	    	break;
	}
}

$smarty->display("masterfile_sku_application_bom.tpl");
exit;

function validate_data(&$form){
	global $hqcon, $LANG, $sessioninfo, $smarty;
	//print "<pre>"; print_r($form);print"</pre>";
	$form['id'] = intval($_REQUEST['id']);
	$form['apply_by'] = $sessioninfo['id'];

	$form['category_id'] = intval($_REQUEST['category_id']);
	if ($form['category_id'] == 0){
		$err['top'][] = $LANG['SKU_INVALID_CATEGORY'];	
	}
	
	$form['brand_id'] = intval($_REQUEST['brand_id']);
	if ($form['brand_id'] == 0){
		$err['top'][] = $LANG['SKU_INVALID_BRAND'];	
	}
	
	if($_REQUEST['sku_type'] == ''){
		$err['top'][] = $LANG['SKU_INVALID_TYPE'];	
	}
	
	$hqcon->sql_query("select id from sku where brand_id=$form[brand_id] and category_id=$form[category_id] and is_bom");
	$r = $hqcon->sql_fetchrow();
	if($r){
		$err['top'][] = sprintf($LANG['SKU_BOM_EXIST'], $r['id']);			
	}	
	return $err;
}

function init_selection(){
	global $con, $sessioninfo, $smarty, $depts, $config;
	// manager and above can see all department
	if ($sessioninfo['level'] < 9999){
		if (!$sessioninfo['departments'])
			$depts = "id in (0)";
		else
			$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
	}
	else{
		$depts = 1;
	}
	// show department option
	$con->sql_query("select id, description from category where active and level = 2 and $depts order by description");
	$smarty->assign("dept", $con->sql_fetchrowset());
	
	// show brand option
	$br = ($sessioninfo['brands']) ? "id in (".join(",",array_keys($sessioninfo['brands'])).") and" : "";
	$con->sql_query("select id, description from brand where $br active order by description");
	$smarty->assign("brand", $con->sql_fetchrowset());
	
	//Show sku_type option
	$con->sql_query("select * from sku_type");
	$smarty->assign('sku_type_list', $con->sql_fetchrowset());
	$con->sql_freeresult();
}

?>
