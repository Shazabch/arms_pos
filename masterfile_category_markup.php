<?php
/*
11/18/2008 3:05:41 PM yinsee
- add filter branch active=1
- add sort by category description

6/24/2011 4:44:48 PM Andy
- Make all branch default sort by sequence, code.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

//print_r($_REQUEST);

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	
	    case 'updateField':
			updateField();
			exit;
		case 'load_child':
		    load_child();
		    exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;

	}
}
load_markup(0);
load_cat(0);
get_branch_list();

$smarty->assign('PAGE_TITLE', 'Category Markup %');
$smarty->display("masterfile_category_markup.tpl");
exit;

function get_branch_list(){
	global $con,$smarty;

	$con->sql_query('select * from branch where active=1 order by sequence,code') or die(mysql_error());
	while($r = $con->sql_fetchrow()){
		$branch_list[$r['id']] = $r;
	}
	$smarty->assign('branch_list',$branch_list);
}

function load_cat($root_id){
	global $con,$smarty;
	
	$q_cat = $con->sql_query("select id,root_id,level,description,code,tree_str,(select count(*) from category c where c.root_id=category .id) as downline_count from category where active=1 and root_id=".mi($root_id)." order by level, description") or die(mysql_error());
	$temp = $con->sql_fetchrowset($q_cat);

	foreach($temp as $r){
		$category[$r['id']] = $r;
	}

	$smarty->assign('category',$category);
}

function load_markup($root_id){
	global $con,$smarty,$sessioninfo;
	
	if (BRANCH_CODE != 'HQ'){
		$filter = " and branch_id=".mi($sessioninfo['branch_id']);
	}
	$sql = "select category_markup.* from category_markup left join category on category_markup.category_id=category.id where root_id=".mi($root_id)." $filter";
	$q_m = $con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow($q_m)){
		$markup[$r['branch_id']][$r['category_id']] = $r;
	}
	$smarty->assign('markup',$markup);
}

function load_child(){
    global $con,$smarty,$sessioninfo;

	$root_id = intval($_REQUEST['root_id']);
	load_cat($root_id);
	load_markup($root_id);
	get_branch_list();
	$smarty->display('masterfile_category_markup.cat.tpl');
}

function updateField(){
	global $con,$smarty,$sessioninfo;
	
	$upd['markup'] = intval($_REQUEST['value']);
	$upd['category_id'] = intval($_REQUEST['cat_id']);
	$upd['branch_id'] = intval($_REQUEST['bid']);
	if (BRANCH_CODE != 'HQ'&&$upd['branch_id']!=$sessioninfo['branch_id']){
		print "Error: Invalid Branch";
		exit;
	}
	$sql = "replace into category_markup ".mysql_insert_by_field($upd);
	$q_up = $con->sql_query($sql) or die(mysql_error());
}

?>
