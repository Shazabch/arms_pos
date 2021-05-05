<?
/*
Revision History
================
5/7/2007 7:20:57 PM   - yinsee
- use category_cache for parent category linking

6/19/2007 4:54:17 PM - yinsee
- for level 2, department_id is self

7/25/2007 2:41:13 PM - yinsee
- CODE is optional
- AREA is optional

11/30/2009 11:57:15 AM - edward
- alter category discount and reward point on category

12/9/2009 4:47:24 PM - edward
- clear category point and discount if level > 3

1/22/2010 2:46:56 PM Andy
- Add index to column when create category cache table

3/1/2010 5:40:14 PM Andy
- Add update category changed if move category

4/23/2010 2:42:12 PM Andy
- Prompt user to confirm if user try to move LINE or DEPARTMENT.
- Delete old department approval flow.
- Delete old allowed department at user privileges.
- Update all documents from old department to new department.

4/26/2010 12:57:29 PM Andy
- make category discount able to save empty

5/6/2010 9:40:45 AM Andy
- Fix update department script.

8/13/2010 10:00:26 AM Andy
- Add SKU without inventory. (control by category + sku and can be inherit)
- Add Fresh Market Sku. (control by category + sku and can be inherit)(Need Config)

8/19/2010 2:56:40 PM Andy
- Add config control to no inventory sku.

12/22/2010 5:15:25 PM Andy
- Fix category discount save bugs, empty should save as null.
- Fix when first time add new category, it will not save category discount

12/22/2010 5:20:58 PM Andy
- Add member category discount.

3/18/2011 10:47:15 AM Alex
- add checking on min_sku_photo when level = 1

5/3/2012 6:03:38 PM Andy
- Add Member Category Discount and Category Reward Point can be set by member type, by branch.

6/29/2012 2:00 PM Andy
- Fix migrate old non-member category discount bug.

1/18/2012 4:10 PM Andy
- Add Staff Discount (need privilege CATEGORY_STAFF_DISCOUNT_EDIT to edit).

2/19/2013 11:31 AM Fithri
- prevent update for discount, reward point & staff discount if user do not have privilege

6/20/2013 3:56 PM Andy
- Remove update vendor_sku when move LINE/DEPARTMENT.

7/12/2013 3:57 PM Andy
- Enhance to check & auto generate category_cache when found the table is not exists.

8/20/2014 5:55 PM DingRen
- add Input Tax, Output Tax, Inclusive Tax

9/9/2014 11:43 AM Fithri
- when edit / create new Department (category level 2), can select allowed user

11/8/2014 10:35 AM Justin
- Enhanced to add config checking while loading gst list.

11:09 AM 12/2/2014 Andy
- Enhance to generate category cache for gst inclusive tax, input and output tax when add/edit or regen category tree.

12/31/2014 9:17 AM Justin
- Enhanced to show inherit info.

2/16/2015 3:59 PM Andy
- Move the rebuild category cache function (build_category_cache, sync_cat_inheritance, sync_cat_inheritance_using_id) to functions.php

3/5/2015 11:54 AM Andy
- Fix check_gst_status to use 'check_only_need_active'.

3/6/2015 3:27 PM Andy
- Enhanced the modules to check when get sku/category gst, no need to check force zero rate.

3/26/2015 6:12 PM Justin
- Bug fixed gst info capture wrongly while config is not turned on.

7/27/2015 2:11 PM Joo Chia
- Clear all discount, reward point, and staff discount if category is moved to level 4 and below.

9/28/2015 5:23 PM DingRen
- add new field "Can Auto load all po items for GRN" (grn_auto_load_po_items)

5/11/2016 4:17 PM Andy
- Fix wrong department_id when create new level 2 category.

8/11/2016 9:50 AM Andy
- Fixed category discount should set as null when move to more than level 3.

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

1/9/2017 3:19 PM Andy
- Enhanced to only allow new customer to choose selling price inclusive tax = yes.
- Enhanced to not allow to edit selling price inclusive tax if it is already using 'inherit' or 'yes'.

9/7/2017 3:53 PM Justin
- Enhanced to have new feature "Use Matrix".

10/22/2018 6:06 PM Justin
- Enhanced to load SKU Type list from database instead of hardcoded it.

2/10/2020 5:55 PM Willliam
- Enhanced to change default value "SKU Without Inventory" of create new category to "No" when category level is 1. 

11/10/2020 9:32 AM Willliam
- Enhanced to add new checkbox "show_in_suite_pos".

2/4/2021 4:05 PM Shane
- Added Promotion / POS Image upload.

2/5/2021 1:18 PM Andy
- Increased maintenance version checking to v488.

2/9/2021 11:43 AM Shane
- Added Promotion / POS Image upload during create category.

4/5/2021 4:00 PM Shane
- Added "Hide category at POS" option.
*/
include("include/common.php");

//if($_REQUEST['a']!='migrate_old_discount_and_point_column'){
	if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
	if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
//}

$maintenance->check(488);
ini_set("memory_limit", "512M");

$smarty->assign("PAGE_TITLE", "Categorization Master File");

// load sku type list
load_sku_type_list();
$inherit_options = array('yes'=>'Yes', 'no'=>'No');
$smarty->assign('inherit_options', $inherit_options);

if($config['enable_one_color_matrix_ibt']){
	$clr_list = get_matrix_color();
	$size_list = get_matrix_size();
	
	$smarty->assign("clr_list", $clr_list);
	$smarty->assign("size_list", $size_list);
}

if (isset($_REQUEST['a']))
{
	$id = intval($_REQUEST['id']);
	
	switch ($_REQUEST['a'])
	{
	    case 'export_csv':
            if (!privilege('MST_CATEGORY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_CATEGORY', BRANCH_CODE), "/index.php");

			header("Content-type: application/x-csv");
			header("Content-Disposition: attachment; filename=category".time().".csv");
	        snyc_category_hier(0);
	        exit;

		case 'build_cache':
			build_category_cache();
			exit;
			
	    case 'sync':
	        if (!privilege('MST_CATEGORY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_CATEGORY', BRANCH_CODE), "/index.php");
			snyc_category_hier();
	        break;

	    case 'ajax_get_row':
	        $con->sql_query("select * from category where id = $id");
	        $r = $con->sql_fetchrow();
	        $r['min_sku_photo'] = unserialize($r['min_sku_photo']);
			$r2 = $con->sql_query("select count(*) from category where root_id = $r[id]");
			$rc = $con->sql_fetchrow();
			$r['child_count'] = $rc[0];
			$smarty->assign("category_row", $r);
			$smarty->display("masterfile_category_row.tpl");
	        exit;

		case 'ajax_get_subcategory':
	        show_table($id, false);
	        exit;

        // move category to another root
		case 'ajax_move_category':
		    if (!privilege('MST_CATEGORY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_CATEGORY', BRANCH_CODE), "/index.php");
		    
		    $con->sql_query("select * from category where id=$id");
		    $cat = $con->sql_fetchrow();
		    
		    if(!$cat)   exit;
		    if($cat['level']<=2){   // moving category is line or department
		        $changed_dept = array();
				if($cat['level']==2)    $changed_dept[] = $id;  // dept
				elseif($cat['level']==1){   // line
					$con->sql_query("select id from category where root_id=$id and level=2");    // find dept under this line
					while($r = $con->sql_fetchrow()){
                        $changed_dept[] = $r['id'];
					}
				}
			}
			
		    // assign new root (id & root_id)
			$con->sql_query("update category set root_id = ".mi($_REQUEST['root_id'])." where id = $id");
			
/*
		    // get the target tree_str
		    $con->sql_query("select level, tree_str from category where id = ".mi($_REQUEST['root_id']));
		    $r2 = $con->sql_fetchrow();
		    $r2['tree_str'] .= "(" . mi($_REQUEST['root_id']) . ")";
            $newdept = get_dept_id($r2['tree_str']);

			// update all level and tree_str
			$con->sql_query("update category set level = level + ($r2[level] + 1 - $r1[level]), tree_str = replace(tree_str, '$r1[tree_str]', '$r2[tree_str]'), department_id = $newdept where tree_str like '$r1[tree_str]($id)%' or id = $id");
			// print("update category set level = level + ($r2[level] - $r1[level]), tree_str = replace(tree_str, '$r1[tree_str]', '$r2[tree_str]') where tree_str like '$r1[tree_str]($id)%' or id = $id");
*/
			show_table(intval($_REQUEST['root_id']), false);
			ob_flush();

			log_br($sessioninfo['id'], 'MASTERFILE', 0, "Category $id moved to root $_REQUEST[root_id]");
			
			// set changed for category move from
			update_category_changed($id);
			//build_category_tree($id);
			snyc_category_hier();
			// set changed for category move to
			update_category_changed($id);

			if($config['enable_no_inventory_sku']){
            	sync_cat_inheritance('no_inventory', $id);  // recreate category cache for column no_inventory
            }
            if($config['enable_fresh_market_sku']){
                sync_cat_inheritance('is_fresh_market', $id);   // recreate category cache for column is_fresh_market
			}
            
            
			//clear category point and discount if level > 3
		    $con->sql_query("select * from category where id =$id");
		    $r1 = $con->sql_fetchrow();
			
			if($r1['level'] > 3)
			$con->sql_query("update category set category_point='',category_disc=null,member_category_disc=null,
							category_disc_by_branch='N;',category_point_by_branch='N;',category_staff_disc_by_branch='N;' where id = $id");

			if($changed_dept){  // update old dept to new dept
				$con->sql_query("select p2 from category_cache where category_id=$id");
				$new_dept_id = mi($con->sql_fetchfield(0));
				if($new_dept_id){
					foreach($changed_dept as $old_dept_id){
					    $old_dept_id = mi($old_dept_id);
						if($new_dept_id==$old_dept_id)  continue;
						// delete approval flow
						$con->sql_query("delete from approval_flow where sku_category_id=$old_dept_id");
						
						// update user allowed dept, remove old dept
						$con->sql_query("select id,departments from user");
						while($r = $con->sql_fetchrow()){
							$upd = array();
							$upd['departments'] = unserialize($r['departments']);
							if($upd['departments'][$old_dept_id]){
								unset($upd['departments'][$old_dept_id]);
								$upd['departments'] = serialize($upd['departments']);
								$con->sql_query("update user set ".mysql_update_by_field($upd)." where id=".mi($r['id']));
							}
						}
						// update adj
						$con->sql_query("update adjustment set dept_id=$new_dept_id where dept_id=$old_dept_id");
						// gra
						$con->sql_query("update gra set dept_id=$new_dept_id where dept_id=$old_dept_id");
						// grn
						$con->sql_query("update grn set department_id=$new_dept_id where department_id=$old_dept_id");
						// grr
						$con->sql_query("update grr set department_id=$new_dept_id where department_id=$old_dept_id");
						// po
						$con->sql_query("update po set department_id=$new_dept_id where department_id=$old_dept_id");
						// vendor_sku
						//$con->sql_query("update vendor_sku set department_id=$new_dept_id where department_id=$old_dept_id");
					}
				}
			}
			exit;
	    // add
		/*case 'a':
		    if (!privilege('MST_CATEGORY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_CATEGORY', BRANCH_CODE), "/index.php");
		    
			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				$con->sql_query("insert into category " . mysql_insert_by_field($form, array('root_id', 'level', 'tree_str', 'department_id', 'code', 'description', 'min_sku_photo', 'area', 'grn_po_qty', 'grn_get_weight','no_inventory','is_fresh_market','category_disc','member_category_disc'), true));
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Category create ' . $form['code']);

				print "<script>parent.window.refresh_sub($form[root_id]);parent.window.hidediv('ndiv');\nalert('$LANG[MSTCAT_NEW_RECORD_ADDED]');</script>\n";
			}
			exit;
			
		// edit
		case 'e':
			if (!privilege('MST_CATEGORY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_CATEGORY', BRANCH_CODE), "/index.php");

			$con->sql_query("select * from category where id = $id");
			if ($con->sql_numrows()<=0)
			{
				print "<script>alert('Invalid category ID: $id');</script>\n";
				exit;
			}
			
			$r = $con->sql_fetchrow();
			$arr = array("code", "description", "tree_str", "area","category_disc","category_point","grn_po_qty", "level", "root_id", 'grn_get_weight','no_inventory','is_fresh_market','member_category_disc');
			$r['min_sku_photo'] = unserialize($r['min_sku_photo']);
			if ($r['min_sku_photo'])
			{
				foreach ($r['min_sku_photo'] as $k => $v)
				{
				    $arr[] = "min_sku_photo[$k]";
				    $r["min_sku_photo[$k]"] = $v;
				}
			}
			irs_fill_form("f_b", $arr, $r);
			exit;*/
		// activate/deactivate
		case 'v':
			if (!privilege('MST_CATEGORY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_CATEGORY', BRANCH_CODE), "/index.php");

			$con->sql_query("update category set active = ".mb($_REQUEST['v'])." where id = $id");
			if ($_REQUEST['v'])
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Category activate ' . $_REQUEST['code']);
			else
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Category deactivate ' . $_REQUEST['code']);
			load_table($id);
			exit;
		
		// show tree
		//case 'rt':
		//	build_category_tree(intval($_REQUEST['vid']));
		//	exit;
		// update
		/*case 'u':
			if (!privilege('MST_CATEGORY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_CATEGORY', BRANCH_CODE), "/index.php");

			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				// store basic info
				$update_col = array('code', 'description', 'min_sku_photo', 'area', 'category_disc', 'category_point', 'grn_po_qty', 'grn_get_weight', 'root_id', 'no_inventory','member_category_disc');
				if($config['enable_fresh_market_sku'])  $update_col[] = 'is_fresh_market';
				
				$con->sql_query("update category set ".mysql_update_by_field($form, $update_col, true)." where id = $id");

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
					
					if($uqf['no_inventory']){   // got update column no_inventory
						sync_cat_inheritance('no_inventory', $id, $form['no_inventory']);
					}
					if($uqf['is_fresh_market']){   // got update column is_fresh_market
						sync_cat_inheritance('is_fresh_market', $id, $form['is_fresh_market']);
					}

					log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Category update information ' . $form['code'] . $changes);
					load_table($id);
					// saved. back to front page
					print "<script>parent.window.hidediv('ndiv');\nalert('$LANG[MSTCAT_DATA_UPDATED]');</script>";
				}
				else
					print "<script>parent.window.hidediv('ndiv');alert('$LANG[NO_CHANGES_MADE]');</script>";
			}
			exit;*/
		case 'open_cat':
			open_cat();
			exit;
		case 'save_cat':
			save_cat();
			exit;
		case 'migrate_old_discount_and_point_column':
			migrate_old_discount_and_point_column();
			exit;
		case 'add_photo':
			add_photo();
			exit;
		case 'ajax_remove_photo':
			ajax_remove_photo();
			exit;
		default:
			print "<h3>Unhandled Request</h3>";
			
			exit;
	}
}

check_and_create_category_cache();
show_table(0);
$smarty->display("masterfile_category_index.tpl");

function show_table($root, $sql_only = true)
{
	global $con, $sessioninfo, $smarty;
 	// create tree
	if (privilege('MST_CATEGORY'))
	{
	    // view all
		$r1 = $con->sql_query("select * from category where root_id = $root order by description");
	}
	else
	{
	    $depts = join(",", array_keys($sessioninfo['departments']));
	    if ($root == 0)
	    {
			// only view category that the user have privilege
	        $r1 = $con->sql_query("select * from category where root_id = $root and id in (select root_id from category where id in ($depts)) order by description");
		}
		else
		{
			// get the lines
			$r1 = $con->sql_query("select * from category where root_id = $root and (id in ($depts) or department_id in ($depts)) order by description");
		}
	}
	if ($con->sql_numrows()<=0) return;
	$cats = array();
	while ($r = $con->sql_fetchrow($r1))
	{
		$r['min_sku_photo'] = unserialize($r['min_sku_photo']);
		$con->sql_query("select count(*) from category where root_id = $r[id]");
		$rc = $con->sql_fetchrow();
		$r['child_count'] = $rc[0];
		array_push($cats, $r);
	}
	$smarty->assign("categories", $cats);

	if (!$sql_only) $smarty->display("masterfile_category_table.tpl");
	return $cats;
}

/*
function build_category_tree($visible_id = 0)
{
	global $con, $smarty, $sessioninfo;

		    
	// create tree
	if (privilege('MST_CATEGORY'))
	{
	    // view all
	    $con->sql_query("select * from category order by description");
	}
	else
	{
	    // only view category that the user have privilege
		$depts = join(",", array_keys($sessioninfo['departments']));
		$con->sql_query("select * from category where level = 1 or id in ($depts) or department_id in ($depts) order by description");
	}

	// todo
	$cat_tree = array();
	while($r = $con->sql_fetchrow())
	{
	    $cat_tree[$r['root_id']][$r['id']] = $r;
	}

	build_tree(0, 0, $cat_tree, '(0)');
	//$smarty->assign("categories", $con->sql_fetchrow());
}

function build_tree($root, $level, &$cat_tree, $tree_str)
{
	global $con;
	
	if ($root == 0)
	{
		print "<h2 id=\"item[0]\"><img src=\"ui/collapse.gif\" onclick=\"do_toggle(this, 'root[0]')\" align=absmiddle> Root <img src=\"ui/add_child.png\" onclick=\"add(0,'(0)',1)\" align=absmiddle title=\"create Sub-category\"></h2>";
	/*	print "<script>";
		print "Droppables.remove('item[0]');\n";
		print "Droppables.add('item[0]',
		{
			accept: 'item',
			hoverclass: 'droptarget',
			onDrop: function(e, t)
			{
			    do_move(0, e, t);
			}
		});\n";
		print "</script>\n";*/
/*	}
	
	$disp = "";
	if ($level > 1) $disp = "display:none";
	
	print "<ul id=\"root[$root]\" style=\"$disp\">\n";
	if ($cat_tree[$root])
	{
		foreach ($cat_tree[$root] as $id => $row)
		{
			print "<li class=\"item\" id=\"item[$id]\">";
			if ($level <= 2) print "<h".($level+3).">";
			if ($cat_tree[$id])
				if ($level >0)
					print "<img src=\"ui/expand.gif\" onclick=\"do_toggle(this, 'root[$id]')\" width=9 height=9 align=absmiddle>";
				else
					print "<img src=\"ui/collapse.gif\" onclick=\"do_toggle(this, 'root[$id]')\" width=9 height=9 align=absmiddle>";
			else
				print "<img src=\"ui/pixel.gif\" width=9 height=9 align=absmiddle>";

			//print " <img id=\"item_handle[$id]\" style=\"cursor:move\" src=\"ui/move.png\" align=absmiddle>";
			if (privilege('MST_CATEGORY'))
				print " <a href=\"javascript:void(ed($id))\" title=\"$row[code]\">";
			print "$row[description]";

			if (privilege('MST_CATEGORY'))
			{
				print "</a>";
				print " <img src=\"ui/add_child.png\" style=\"cursor:pointer\"onclick=\"add($id,'$tree_str($id)',".($level+2).")\" align=absmiddle title=\"create Sub-category\">";
			}
			
			if ($level <= 2) print "</h".($level+3).">";
            build_tree($id, $level+1, $cat_tree, $tree_str."($id)");

			if ($_REQUEST['sync'])
			{
			    $newdept = get_dept_id($tree_str);
            	$con->sql_query("update category set level = $level+1, tree_str =  '$tree_str', department_id = $newdept where id = $id");
            }
            
			print "</li>\n";*/
	/*		print "<script>";
			print "new Draggable('item[$id]',{revert:true, ghosting:true, handle: 'item_handle[$id]'});\n";
			print "Droppables.remove('item[$id]');\n";
			print "Droppables.add('item[$id]',
			{
				accept: 'item',
				hoverclass: 'droptarget',
				onDrop: function(e, t)
				{
				    do_move($id, e, t);
				}
			});\n";
			print "</script>\n";*/
/*
		}
	}
//	print "<li><a href=\"javascript:void(add($root, $level))\">Add</a></li>\n";
	print "</ul>\n";
	
}
*/
function load_table($id)
{
	print "<script>parent.window.refresh_row($id);</script>";
}

function validate_data(&$form)
{
	global $LANG, $con, $id, $config;

	$errm = array();
		
	if ($form['code'] != '')
	{
		$form['code'] = strtoupper($form['code']);
		// if old code != new code, check new code exists
		$con->sql_query("select * from category where id <> $id and code = " . ms($form['code']));
		
		if ($con->sql_numrows() > 0)
		{
			$errm[] = sprintf($LANG['MSTCAT_CODE_DUPLICATE'], $form['code']);
		}
	}
	
	if ($form['description'] == '')
		$errm[] = $LANG['MSTCAT_DESCRIPTION_EMPTY'];
	//if ($form['area'] == '')
	//	$errm[] = $LANG['MSTCAT_AREA_EMPTY'];
	
	
	if($config['enable_no_inventory_sku']||$config['enable_fresh_market_sku']){
        if($form['level']==1&&(($config['enable_no_inventory_sku']&&$form['no_inventory']=='inherit')||($config['enable_fresh_market_sku']&&$form['is_fresh_market']=='inherit'))){
			$errm[] = $LANG['MSTCAT_LINE_CANNOT_USE_INHERIT'];
		}
	}
	
	if (!$errm){
		if($form['level']==1){
			foreach ($form['min_sku_photo'] as $sku_type => $value){
				if ($value < 0 ){
					$errm[] = $LANG['MSTCAT_LINE_CANNOT_USE_INHERIT'];
					break;
				}
			}
		}
	}
	
	if (check_gst_status(array('check_only_need_active'=>1)))
	{
		if ($form['input_tax'] == '') $errm[] = $LANG['GST_INPUT_TAX_EMPTY'];
		elseif ($form['output_tax'] == '') $errm[] = $LANG['GST_OUTPUT_TAX_EMPTY'];
		elseif ($form['inclusive_tax'] == '') {
			//$errm[] = $LANG['GST_INCLUSIVE_TAX_EMPTY'];
			$form['inclusive_tax'] = 'inherit';
		}
	}

    //$form['department_id'] = get_dept_id($form['tree_str'],$id);
    if($form['root_id']){
		$parent = get_category_info($form['root_id']);
		if($parent){
			$form['department_id'] = $parent['department_id'];
			$form['tree_str'] = $parent['tree_str']."(".$form['root_id'].")";
		}else{
			$errm[] = "Invalid Parent Category ID#".$form['root_id'];
		}
	}else{
		$form['tree_str'] = "(0)";
	}
	$form['min_sku_photo'] = serialize($form['min_sku_photo']);
	
	// clone category discount to old column
	$form['member_category_disc'] = $form['category_disc_by_branch'][0]['member']['global'];
	$form['category_disc'] = $form['category_disc_by_branch'][0]['nonmember']['global'];
	// clone category point to old column
	$form['category_point'] = $form['category_point_by_branch'][0]['global'];
	 
	$form['category_disc_by_branch'] = serialize($form['category_disc_by_branch']);
	$form['category_point_by_branch'] = serialize($form['category_point_by_branch']);
	$form['category_staff_disc_by_branch'] = serialize($form['category_staff_disc_by_branch']);
	
	return $errm;
}

function get_dept_id($tree_str, $id)
{
	if (preg_match("/^\(\d+\)\(\d+\)\((\d+)\)/", $tree_str, $matches))
		return intval($matches[1]);
	return $id;
}

function snyc_category_hier($sync=1, $front = '', $root_id = 0, $tree_array = array(0), $level = 1)
{
	global $con;

    $tree_str = "(".join(")(",$tree_array).")";
	
	$res = $con->sql_query("select id, description from category where root_id = $root_id");
	while($r=$con->sql_fetchrow($res))
	{
		if ($sync)
		{
		    $newdept = get_dept_id($tree_str,$r[0]);
        	$con->sql_query("update category set level = $level, tree_str =  '$tree_str', department_id = $newdept where id = $r[0]");

        }
		else
		{
			print "$r[0],$front$r[1]\n";
		}
		$new_tree = $tree_array ;
		$new_tree[] = $r[0];
		snyc_category_hier($sync, "$front$r[1],",$r[0], $new_tree, $level + 1);
	}


	if ($sync && $root_id==0){
	    build_category_cache();
	}
}

function open_cat(){
	global $con, $smarty, $LANG, $config, $sessioninfo, $global_gst_settings;
	
	if (!privilege('MST_CATEGORY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_CATEGORY', BRANCH_CODE), "/index.php");
	
	$cat_id = mi($_REQUEST['cat_id']);
	$root_cat_id = mi($_REQUEST['root_cat_id']);
	
	load_branch_list();
	
	if($cat_id>0){	// is edit
		$con->sql_query("select * from category where id = $cat_id");
		if ($con->sql_numrows()<=0)
		{
			print "<script>alert('Invalid category ID: $cat_id');</script>\n";
			exit;
		}
		
		$form = $con->sql_fetchassoc();
		$form['min_sku_photo'] = unserialize($form['min_sku_photo']);
		$form['category_disc_by_branch'] = unserialize($form['category_disc_by_branch']);
		$form['category_point_by_branch'] = unserialize($form['category_point_by_branch']);
		$form['category_staff_disc_by_branch'] = unserialize($form['category_staff_disc_by_branch']);

		$group_num = ceil($cat_id/10000);
		$photo_abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/category_photo/".$group_num."/".$cat_id."/1.jpg";
		if(file_exists($photo_abs_path)){
			$imagep = str_replace($_SERVER['DOCUMENT_ROOT']."/", "", $photo_abs_path);
			$form['cat_photo'] = $imagep;
			$form['cat_photo_time'] = filemtime($photo_abs_path);
		}else{
			unset($form['cat_photo'],$form['cat_photo_time']);
		}
		$con->sql_freeresult();
	}else{	// is add new
		if($root_cat_id>0){
			$con->sql_query("select * from category where id = $root_cat_id");
			if ($con->sql_numrows()<=0)
			{
				print "<script>alert('Invalid category ID: $root_cat_id');</script>\n";
				exit;
			}
			$parent = $con->sql_fetchassoc();
			$con->sql_freeresult();
		}else	$root_cat_id = 0;
		
		
		$form = array();
		$form['level'] = $parent['level']+1;
		if($form['level'] == 1 && $root_cat_id == 0) $form['no_inventory'] = 'no';
		$form['root_id'] = $root_cat_id;
	}
	//print_r($form);
	
	if($config['enable_gst']){
		$q1 = $con->sql_query("select * from gst where active=1 order by id");

		$input_tax = $output_tax = array();
		while($g=$con->sql_fetchassoc($q1)){
			if($g['type']=='purchase') $input_tax[$g['id']]=$g;
			elseif($g['type']=='supply') $output_tax[$g['id']]=$g;
		}
		$con->sql_freeresult($q1);
	}
	
	//when add new or edit category for level 2, can select allowed user
	if (privilege('MST_CATEGORY_SET_USER'))
	{
		$can_select_user = false;
		if (($cat_id>0 && $root_cat_id==0) || ($cat_id==0 && $root_cat_id>0))
		{
			if ($cat_id) { //means edit category level 2
				//edit allowed-user is for admin only
				if ($sessioninfo['level']>=9999)
				{
					$con->sql_query("select level from category where id=$cat_id");
					$r = $con->sql_fetchassoc();
					if ($r['level'] == '2') {
						$can_select_user = true;
						
						//search for user that already had access to this category
						$con->sql_query("select id from user where departments like '%i:$cat_id;s:2:\"on\";%'");
						while ($r = $con->sql_fetchassoc()) {
							$allowed_user[$r['id']] = 1;
						}
						$smarty->assign('allowed_user', $allowed_user);
					}
				}
			}
			else if ($root_cat_id) { //means add new level 2 category
				$con->sql_query("select level from category where id=$root_cat_id");
				$r = $con->sql_fetchassoc();
				if ($r['level'] == '1') {
					$can_select_user = true;
				}
			}
			else {
			}
			
			if ($can_select_user) {
				$con->sql_query("select u.id, u.u, br.code as bcode,u.is_arms_user
				from user u 
				left join branch br on u.default_branch_id=br.id 
				where br.id and u.active=1 and u.id>0 and u.template!=1 order by u.default_branch_id, u.u");
				while ($r = $con->sql_fetchassoc()) {
					$user_selection[$r['bcode']][] = $r;
				}
				$smarty->assign('user_selection', $user_selection);
				/*
				print '<pre>';
				print_r($user_selection);
				print '</pre>';
				*/
			}
		}
	}

	$smarty->assign('is_gst', check_gst_status(array('check_only_need_active'=>1)));
	$smarty->assign('input_tax', $input_tax);
	$smarty->assign('output_tax', $output_tax);
	
	// look for inherit information
	if($form['level'] > 1){
		$root_info['inclusive_tax'] = get_category_gst("inclusive_tax", $form['root_id']);
		$root_info['input_tax'] = get_category_gst("input_tax", $form['root_id'], array('no_check_use_zero_rate'=>1));
		$root_info['output_tax'] = get_category_gst("output_tax", $form['root_id'], array('no_check_use_zero_rate'=>1));
	}else{
		$root_info['inclusive_tax'] = $global_gst_settings['inclusive_tax'];
		$root_info['input_tax'] = get_gst_settings($global_gst_settings['global_input_tax']);
		$root_info['output_tax'] = get_gst_settings($global_gst_settings['global_output_tax']);
	}
	
	// load dummy data for matrix
	if($config['enable_one_color_matrix_ibt']){
		$form['use_matrix'] = "yes";
		$clr_list = get_matrix_color();
		$size_list = get_matrix_size();
		
		if($clr_list && $size_list){
			foreach($clr_list as $arr1=>$clr){
				foreach($size_list as $arr2=>$size){
					$min_qty = rand(1,5);
					$max_qty = rand(5,10);
					$is_nnr = rand(0,1);
					$form['matrix']['is_nnr'][$size] = $is_nnr;
					$form['matrix']['min_qty'][$clr][$size] = $min_qty;
					$form['matrix']['max_qty'][$clr][$size] = $max_qty;
				}
			}
		}
	}

	$smarty->assign('root_info', $root_info);
	
	$smarty->assign('form', $form);
	$smarty->display('masterfile_category.open.tpl');
}

function save_cat(){
	global $con, $smarty,$LANG,$config,$sessioninfo;
	
	if (!privilege('MST_CATEGORY')) die(sprintf($LANG['NO_PRIVILEGE'], 'MST_CATEGORY', BRANCH_CODE));
	
	$id = mi($_REQUEST['id']);
	
	$form = $_REQUEST;
		
	$errmsg = validate_data($form);
	
    $is_gst=check_gst_status(array('check_only_need_active'=>1));

	if($id){	// update
		$ori_form = get_category_info($id);
		if(!$ori_form)	$errmsg[] = "Invalid Category ID#$id";
	}
	
	if ($errmsg)
	{
		foreach($errmsg as $e){
			print "$e\n";
		}
		exit;
	}
	else
	{
		if($id){	// update
			// get data before update
			$ori_form = get_category_info($id);
			
			$update_col = array('code', 'description', 'min_sku_photo', 'area', 'category_disc', 'category_point', 'grn_po_qty', 'grn_get_weight', 'root_id','member_category_disc', 'grn_auto_load_po_items', 'hide_at_pos');
			
			if($config['enable_fresh_market_sku'])  $update_col[] = 'is_fresh_market';
			if($config['enable_no_inventory_sku'])  $update_col[] = 'no_inventory';
			
			// prevent update column when user do not have privilege
			if ($sessioninfo['privilege']['CATEGORY_DISCOUNT_EDIT']) $update_col[] = 'category_disc_by_branch';
			if ($sessioninfo['privilege']['MEMBER_POINT_REWARD_EDIT']) $update_col[] = 'category_point_by_branch';
			if ($config['membership_enable_staff_card'] && $sessioninfo['privilege']['CATEGORY_STAFF_DISCOUNT_EDIT']) $update_col[] = 'category_staff_disc_by_branch';
			//if ($config['enable_suite_device']) $update_col[] = 'show_in_suite_pos';
			
			if($is_gst){
				$update_col[] = 'input_tax';
				$update_col[] = 'output_tax';
				$update_col[] = 'inclusive_tax';
			}
			
			$con->sql_query("update category set ".mysql_update_by_field($form, $update_col, true)." where id = $id");

			// got update column no_inventory
			if($config['enable_no_inventory_sku'] && $ori_form['no_inventory'] != $form['no_inventory']){   
				sync_cat_inheritance('no_inventory', $id, $form['no_inventory']);
			}

			// got update column is_fresh_market
			if($config['enable_fresh_market_sku'] && $ori_form['is_fresh_market']!=$form['is_fresh_market']){   
				sync_cat_inheritance('is_fresh_market', $id, $form['is_fresh_market']);
			}

			// got update column input_tax
			if($is_gst && $ori_form['input_tax']!=$form['input_tax']){   
				sync_cat_inheritance_using_id('input_tax', $id, $form['input_tax']);
			}
			
			// got update column output_tax
			if($is_gst && $ori_form['output_tax']!=$form['output_tax']){
				sync_cat_inheritance_using_id('output_tax', $id, $form['output_tax']);
			}
			
			// got update column inclusive_tax
			if($is_gst && $ori_form['inclusive_tax']!=$form['inclusive_tax']){   
				sync_cat_inheritance('inclusive_tax', $id, $form['inclusive_tax']);
			}
	
			log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Category update information '.$form['code']." Desc#".$form['description']);
		}else{	// add new
			// check current max level
			$con->sql_query("select max(level) from category");
			$max_lv = mi($con->sql_fetchfield(0));
			$con->sql_freeresult();
			
			$update_col = array('root_id', 'level', 'tree_str', 'department_id', 'code', 'description', 'min_sku_photo', 'area', 'grn_po_qty', 'grn_get_weight','category_disc','member_category_disc', 'grn_auto_load_po_items', 'hide_at_pos');

			if($config['enable_fresh_market_sku'])  $update_col[] = 'is_fresh_market';
			$update_col[] = 'no_inventory';
			
			// prevent insert column when user do not have privilege
			if ($sessioninfo['privilege']['CATEGORY_DISCOUNT_EDIT']) $update_col[] = 'category_disc_by_branch';
			if ($sessioninfo['privilege']['MEMBER_POINT_REWARD_EDIT']) $update_col[] = 'category_point_by_branch';
			if ($config['membership_enable_staff_card'] && $sessioninfo['privilege']['CATEGORY_STAFF_DISCOUNT_EDIT']) $update_col[] = 'category_staff_disc_by_branch';
			/*if ($config['enable_suite_device']){
				$update_col[] = 'show_in_suite_pos';
				if(!$form['show_in_suite_pos']) $form['show_in_suite_pos'] = 0;
			}*/
			
			if($is_gst){
				$update_col[] = 'input_tax';
				$update_col[] = 'output_tax';
				$update_col[] = 'inclusive_tax';
			}

			$con->sql_query("insert into category " . mysql_insert_by_field($form, $update_col, true));
			$new_id = $con->sql_nextid();
			
			log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Category create: Code#' . $form['code']." Desc#".$form['description']);
			
			if($form['level'] == 2){
				// it is department
				$con->sql_query("update category set department_id=".mi($new_id)." where id=$new_id");
			}
			
			if($form['level'] > $max_lv){
				snyc_category_hier();
			}else{
				$con->sql_query("select * from category_cache where category_id=".mi($form['root_id']));
				$cc = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				$cc['category_id'] = $new_id;
				$cc['p'.$form['level']] = $new_id;
				if($config['enable_fresh_market_sku']){
					if($form['is_fresh_market']!='inherit')	$cc['is_fresh_market'] = $form['is_fresh_market'];
				}
				if($config['enable_no_inventory_sku']){
					if($form['no_inventory']!='inherit')	$cc['no_inventory'] = $form['no_inventory'];
				}
				// input_tax
				if($is_gst){
					if($form['input_tax'] >= 0)	$cc['input_tax'] = $form['input_tax'];
				}
				
				// output_tax
				if($is_gst){   
					if($form['output_tax'] >= 0)	$cc['output_tax'] = $form['output_tax'];
				}
				
				// inclusive_tax
				if($is_gst){
					if($form['inclusive_tax']!='inherit')	$cc['inclusive_tax'] = $form['inclusive_tax'];
				}
				$con->sql_query("insert into category_cache ".mysql_insert_by_field($cc));
			}

			if($form['has_tmp_photo']){
				$tmp_photo = $form['tmp_photo'];
				if(file_exists($tmp_photo)){
					$group_num = ceil($new_id/10000);
					$cat_dir = $_SERVER['DOCUMENT_ROOT']."/sku_photos/category_photo";
					check_and_create_dir($cat_dir);

					$photo_dir = $cat_dir."/".$group_num;
					check_and_create_dir($photo_dir);

					$photo_abs_path = $photo_dir."/".$new_id;
					check_and_create_dir($photo_abs_path);

					$filepath = "$photo_abs_path/1.jpg";
					copy($tmp_photo,$filepath);

					if(file_exists($filepath)){
						$con->sql_query("update category set got_pos_photo=1 where id=$new_id");
					}
				}
			}
		}
		
		//update each user departments
		if ($_REQUEST['level'] == '2' && $_REQUEST['allowed_user']) {
			$id = ($id) ? $id : $new_id;
			
			//users who is ticked
			$r11 = $con->sql_query("select id, departments from user where id in (".join(',',array_keys($_REQUEST['allowed_user'])).")");
			while ($r = $con->sql_fetchassoc($r11)) {
				$depts = unserialize($r['departments']);
				$depts[$id] = 'on';
				$con->sql_query("update user set departments=".ms(serialize($depts))." where id=".mi($r['id'])." limit 1");
			}
			
			//user who is previously ticked but has been unticked
			$r22 = $con->sql_query("select u.id, u.departments from user u left join branch br on u.default_branch_id=br.id where br.id and u.active=1 and u.id>0 and u.template!=1 and u.id not in (".join(',',array_keys($_REQUEST['allowed_user'])).") and u.departments like '%i:$id;s:2:\"on\";%'");
			while ($r = $con->sql_fetchassoc($r22)) {
				$depts = unserialize($r['departments']);
				unset($depts[$id]);
				$con->sql_query("update user set departments=".ms(serialize($depts))." where id=".mi($r['id'])." limit 1");
			}
			
		}
		
	}
	
	print "OK";
}

function migrate_old_discount_and_point_column(){
	global $con;
	
	$q1 = $con->sql_query("select * from category order by id");
	$update_count = 0;
	while($r = $con->sql_fetchassoc($q1)){
		$r['category_disc_by_branch'] = unserialize($r['category_disc_by_branch']);
		$r['category_point_by_branch'] = unserialize($r['category_point_by_branch']);
		
		$need_update = false;
		
		// member discount
		if($r['member_category_disc']!=$r['category_disc_by_branch'][0]['member']['global'] || (!$r['category_disc_by_branch'][0]['set_override'] && $r['member_category_disc']!=='')){
			$r['category_disc_by_branch'][0]['member']['global'] = $r['member_category_disc'];
			$r['category_disc_by_branch'][0]['set_override'] = 1;
			$need_update = true;
		}
		
		// non-member discount
		if($r['category_disc']!=$r['category_disc_by_branch'][0]['nonmember'] || (!$r['category_disc_by_branch'][0]['set_override'] && $r['category_disc']!=='')){
			$r['category_disc_by_branch'][0]['nonmember']['global'] = $r['category_disc'];
			$r['category_disc_by_branch'][0]['set_override'] = 1;
			$need_update = true;
		}
		
		// point
		if($r['category_point']!=$r['category_point_by_branch'][0]['global']  || (!$r['category_point_by_branch'][0]['set_override'] && $r['category_point']!=='')){
			$r['category_point_by_branch'][0]['global'] = $r['category_point'];
			$r['category_point_by_branch'][0]['set_override'] = 1;
			$need_update = true;
		}
		
		if($need_update){
			$upd = array();
			$upd['category_disc_by_branch'] = serialize($r['category_disc_by_branch']);
			$upd['category_point_by_branch'] = serialize($r['category_point_by_branch']);
			$con->sql_query("update category set ".mysql_update_by_field($upd)." where id=".mi($r['id']));
			$update_count++;
		}
	}
	$con->sql_freeresult($q1);
	print "$update_count Category Updated.";
}

function check_and_create_category_cache(){
	global $con;
	
	if(!$con->sql_query_false("explain category_cache")){
		build_category_cache();
	}
}

function add_photo(){
	global $config,$con;
	$id = $_REQUEST['id'];

	if($id && $id != 'undefined'){
		$group_num = ceil($id/10000);
		$cat_dir = $_SERVER['DOCUMENT_ROOT']."/sku_photos/category_photo";
		check_and_create_dir($cat_dir);

		$photo_dir = $cat_dir."/".$group_num;
		check_and_create_dir($photo_dir);

		$photo_abs_path = $photo_dir."/".$id;
		check_and_create_dir($photo_abs_path);

		$filepath = "$photo_abs_path/1.jpg";
		$imagep = str_replace($_SERVER['DOCUMENT_ROOT']."/", "", $filepath);
	}else{
		//Put into root tmp folder
		$tmp_dir = "/tmp";
		check_and_create_dir($tmp_dir);
		$filepath = $tmp_dir."/category_photo_tmp_".time().".jpg";
		$imagep = $filepath;
	}

	//remove old photo
	if(file_exists($filepath)) unlink($filepath);

	move_uploaded_file($_FILES['fnew']['tmp_name'], $filepath);
	if (!$config["sku_no_resize_photo"])
		resize_photo($filepath,$filepath);

	
	chmod($imagep,0777);
	$urlenc = urlencode($imagep);
	$current_time = date("Y-m-d H:i:s");

	if($id && $id != 'undefined'){
		//Update got_pos_photo to 1
		$upd = array();
		$upd['got_pos_photo'] = 1;
		$con->sql_query("update category set ".mysql_update_by_field($upd)." where id = ".mi($id));

		//Update log
		$con->sql_query("select description from category where id =".mi($id));
		$r=$con->sql_fetchrow();
		log_br($sessioninfo['id'], 'MASTERFILE', $id, "Category Add Photo ".$r['description']." (ID#$id)");
	}else{
		log_br($sessioninfo['id'], 'MASTERFILE', $id, "Category Add Temporary Photo");
	}

	$ret_str = "<div id=ret>
	<div id=pos_img class=imgrollover>
		<img width=110 height=100 align=absmiddle vspace=4 hspace=4 src=\"/thumb.php?w=110&h=100&img=$urlenc\" border=0 style=\"cursor:pointer\" onclick=\"popup_div('img_full', '<img id=img_full_promo width=640 src=\'$imagep?t=$current_time\' onload=\'center_div(img_full)\'>',10001)\" title=\"View\"><br>
		<img src=\"/ui/del.png\" align=absmiddle onclick=\"if (confirm('Are you sure?'))del_image(this.parentNode,'$urlenc', 0)\"> Delete
		</div>
	</div>
	<script>parent.window.upload_callback(document.getElementById('ret'));";

	//Set form.has_tmp_photo = 1, so that when creating category, can detect if has uploaded an image
	if(!$id || $id == 'undefined'){
		$ret_str .= "parent.window.set_has_tmp_photo('".$filepath."');";
	}

	$ret_str .= "</script>";
	
	print $ret_str;
}

function ajax_remove_photo(){
	global $con;
	$id = mi($_REQUEST['id']);
	if($id < 0) die("Invalid Category ID.");
	
	$f = urldecode($_REQUEST['f']);
	if(!$f)	die("Invalid Image File Path");
	
	if (@unlink($f)){
		if($id){
			$upd = array();
			$upd['got_pos_photo'] = 0;
			$con->sql_query("update category set ".mysql_update_by_field($upd)." where id=$id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Category Removed Photo (ID#$id), Path: $f");
		}else{
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Category Removed Temporary Photo, Path: $f");
		}
		print "OK";
	}
	else
		print "Delete failed $f";
}
?>
