{*
7/25/2007 2:43:20 PM - yinsee
- Code and Area optional

12/1/2009 3:07:50 PM - edward
- add discount and reward point

4/23/2010 2:42:12 PM Andy
- Prompt user to confirm if user try to move LINE or DEPARTMENT.
- Delete old department approval flow.
- Delete old allowed department at user privileges.
- Update all documents from old department to new department.

5/6/2010 9:39:48 AM Andy
- Fix javascript bugs that cause loading icon wont hide.

8/13/2010 10:08:27 AM Andy
- Add SKU without inventory. (control by category + sku and can be inherit)
- Add Fresh Market Sku. (control by category + sku and can be inherit)(Need Config)

8/16/2010 12:40:29 PM Andy
- Fix category moving bugs.

8/19/2010 2:48:30 PM Andy
- Add config control to no inventory sku.

12/16/2010 1:31:01 PM Andy
- Add member category discount.

3/18/2011 11:20:55 AM Alex
- add link to create new Line

5/19/2011 11:19:49 AM Alex
- Check $config.ci_auto_gen_artno to add justkids.js script  
- limit 1 digit for category code for justkids only

5/7/2012 11:12:48 AM Andy
- Add Member Category Discount and Category Reward Point can be set by member type, by branch.
- Reconstruct category add/edit script to use ajax instead of using iframe.
- Fix when add new category, will update parent child count.

1/18/2012 4:10 PM Andy
- Add Staff Discount (need privilege CATEGORY_STAFF_DISCOUNT_EDIT to edit).

4/25/2014 4:42 PM Justin
- Enhanced to centerlise the popup window while edit/add category.

9/9/2014 11:43 AM Fithri
- when edit / create new Department (category level 2), can select allowed user

7/27/2015 2:09 PM Joo Chia
- Prompt alert to clear discount, reward point, and staff discount if category moved to level 4 and below.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

9/7/2017 3:36 PM Justin
- Enhanced to have new feature "Use Matrix".
- Enhanced to allow user check/uncheck min qty by size from matrix.

2/4/2021 4:05 PM Shane
- Added Promotion / POS Image upload.

2/9/2021 11:43 AM Shane
- Added Promotion / POS Image upload during create category.
*}
{include file=header.tpl}
{literal}
<style>
div.imgrollover
{
	float:left;
	height:105px;
	overflow:hidden;
	border:1px solid transparent;
}

div.imgrollover:hover
{
	background:#fff;
	height:130px;
	border:1px solid #999;
}

#upload_popup {
	border:2px solid #000;
	background:#fff;
	width:350px;
	height:165px;
	padding:10px;
	position:absolute;
	text-align:center;
	z-index:10000;
}
</style>
{/literal}
<script type="text/javascript">
var lastn = '';
var move_start_id = '';
var move_start_root_id = '';
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function loaded()
{
	document.getElementById('bmsg').innerHTML = 'Click Update to save changes';
	if(document.f_b.level.value < 4)
		Element.show('category_options');
	else
        Element.hide('category_options');
	if (document.f_b.level.value==2) 
		Element.show('grn_options');
	else
		Element.hide('grn_options');
		//document.f_b.changed_fields.type='text';
	document.f_b.changed_fields.value = '';
	document.f_b.code.focus();

	if (document.f_b.level.value <= 2)
		$('photo_tb').style.display = '';
	else
	    $('photo_tb').style.display = 'none';
}

function do_move(id, root_id, old_root_id)
{
	// remove the tree <tr>
	$('sc'+id).parentNode.remove();
	// replace the current category row with loading icons
	$('r'+id).update("<td colspan=2>"+_loading_+"</td>");

	ajax_request('masterfile_category.php',{
        method: 'get',
		parameters: 'a=ajax_move_category&root_id='+root_id+'&id='+id,
		evalScripts: true,
		onComplete: function () {
            $('r'+id).remove(); // remove the current category row
		    refresh_row(root_id); // refresh the new parent row to show correct sub category count
		    if(old_root_id)	refresh_row(old_root_id);   // refresh the old parent row to show correct sub category count
		    refresh_sub(root_id);   // refresh the new sub category to show the changes
		}
	});
}

function refresh_row(id)
{
	new Ajax.Updater(
	 'r'+id,
	'masterfile_category.php',
	{
	    method: 'get',
		parameters: 'a=ajax_get_row&id='+id,
		evalScripts: true
 	});
	
}

function move(id,tree_str, cat_level, root_id)
{
	// start drag
	if (move_start_id == '')
	{
	    if(cat_level<=2){
			if(cat_level==1)    var c = 'LINE';
			else var c = 'DEPARTMENT'
            if(!confirm('Are you sure to move '+c+'?\n- All approval flow under this '+c+' will be delete.\n- All documents under this '+c+' will change to your new department.\n- Action cannot be undo and data cannot be revert once you confirm the move.'))  return false;
		}
	    
	    move_start_id = id;
	    move_start_root_id = root_id;
	    alert('Click on the Move icon of the targetted parent to move this category');
	}
	else
	{
		if (id == move_start_id || tree_str.indexOf('('+move_start_id+')')>=0)
		{
		    alert('Cannot move a category to itself or its sub-category');
		}
	    else
		{
			var confirm_text = 'Are you sure to move under this category?';
			
			if(cat_level>=3) 
				confirm_text += '\nAll Discount, Reward point, and Staff discount will be cleared.';
				
		    if (confirm(confirm_text))
				do_move(move_start_id, id, move_start_root_id)
		}
	    move_start_id = '';
	}
}

// toggle view of subcategory
function toggle_sub(id)
{
	if ($('sc'+id).style.display == "")
	{
	    $('sc'+id).style.display = "none";
	    $('sc'+id).innerHTML = "";
	    return;
	}
	else
		refresh_sub(id);
}

// reload the subcategory
function refresh_sub(id)
{
    $('sc'+id).innerHTML = '<p align=center><img src=ui/clock.gif align=absmiddle> Loading...</p>';
    $('sc'+id).style.display = '';
    
	new Ajax.Updater(
	'sc'+id,
	'masterfile_category.php',
	{
	    method: 'get',
		parameters: 'a=ajax_get_subcategory&id='+id,
		evalScripts: true,
		onComplete: function () {
		    if ($('sc'+id).innerHTML == '')
		    {
		        alert('This item has no sub-category');
		        $('sc'+id).innerHTML = '';
		        $('sc'+id).style.display = 'none';
			}
			//else
			//	new Effect.Appear('sc'+id, {duration:0.2});
			
			var childs = $$('.tr_child_of-'+id);
			$('span_child_count-'+id).update("("+childs.length+")")
		}
 	});
}

function ed(n)
{
	/*document.getElementById('abtn').style.display = 'none';
	document.getElementById('ebtn').style.display = '';
	document.getElementById('bmsg').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	showdiv('ndiv');
	document.f_b.reset();
	document.f_b.id.value = n;
	_irs.document.location = '?a=e&id='+n;
	lastn = n;

	document.f_b.a.value = 'u';
	document.f_b.code.focus();*/
	
	open_cat(n);
}

function open_cat(cat_id, root_cat_id){
	if (check_login()) {
        curtain(true);
		if(!root_cat_id)	root_cat_id = 0;
		var params = {
			a:"open_cat",
			cat_id: cat_id,
			root_cat_id: root_cat_id
		};

		$('div_open_cat').update(_loading_);
		new Ajax.Updater('div_open_cat',phpself, {
			parameters:params,
			evalScripts:true
		});
		$('ndiv').show();
		//center_div('ndiv');
	}
}

function add(root_id, tree_str, level)
{
	open_cat(0, root_id);
	
	/*showdiv('ndiv');
	document.getElementById('abtn').style.display = '';
	document.getElementById('ebtn').style.display = 'none';
	document.getElementById('bmsg').innerHTML = 'Enter the following and click ADD';
	document.f_b.reset();
	document.f_b.a.value = 'a';
	document.f_b.id.value = 0;
	document.f_b.level.value = level;
	document.f_b.root_id.value = root_id;
	document.f_b.tree_str.value = tree_str;
	document.f_b.code.focus();
	if (level <= 2)
		$('photo_tb').style.display = '';
	else
	    $('photo_tb').style.display = 'none';

	if (document.f_b.level.value==2) 
		Element.show('grn_options');
	else
		Element.hide('grn_options');*/
}

function do_toggle(obj, id)
{
	togglediv(id);
	if ($(id).style.display == 'none')
	    obj.src = 'ui/expand.gif'
	else
	    obj.src = 'ui/collapse.gif'
}

function act(n, s)
{
	_irs.document.location = '?a=v&id='+n+'&v='+s;
}

function check_b()
{
	/*if (empty(document.f_b.code, 'You must enter Code'))
	{
		return false;
	}*/
	if (empty(document.f_b.description, 'You must enter Description'))
	{
		return false;
	}
/*	if (empty(document.f_b.area, 'You must enter Area'))
	{
		return false;
	}*/
	return true;
}

function category_discount_branch_override_changed(bid){
	var c = $('inp_category_disc_override-'+bid).checked;
	$(document.f_b).getElementsBySelector("input.inp_category_disc-"+bid).each(function(inp){
		inp.disabled = !c;
	});
}

function cat_disc_value_changed(inp){
	var v = inp.value.trim();
	
	if(v=='')	inp.value='';
	else{
		mf(inp,2);
		v = float(inp.value);
		if(v>100)	inp.value = '100.00';
		else if(v<=0){
			inp.value = 0;
		}
	}
}

function curtain_clicked(){
	$('ndiv').hide();
}

function save_category(){
	if(!check_b())	return false;
	
	$('btn_save_cat').value = "Saving...";
	
	$$('#tr_btn_row input').each(function(inp){
		inp.disabled = true;
	});
	
	ajax_request(phpself, {
		parameters: $(document.f_b).serialize(),
		onComplete: function(e){
			if(e.responseText.trim()!='OK'){
				alert(e.responseText);
				$$('#tr_btn_row input').each(function(inp){
					inp.disabled = false;
				});				
				$('btn_save_cat').value = 'Save';
			}else{
				if(document.f_b['root_id']){
					var root_id = document.f_b['root_id'].value;
					refresh_sub(root_id);
				}	
				default_curtain_clicked();
			}
		}
	});
}

function category_point_value_changed(inp){
	var v = inp.value.trim();
	
	if(v=='')	inp.value='';
	else{
		mf(inp,2);
		v = float(inp.value);
		if(v<=0){
			inp.value = 0;
		}
	}
}

function category_point_branch_override_changed(bid){
	var c = $('inp_category_point_override-'+bid).checked;
	$(document.f_b).getElementsBySelector("input.inp_category_point-"+bid).each(function(inp){
		inp.disabled = !c;
	});
}

function category_staff_discount_branch_override_changed(bid){
	var c = $('inp_category_staff_disc_override-'+bid).checked;
	$(document.f_b).getElementsBySelector("input.inp_category_staff_disc-"+bid).each(function(inp){
		inp.disabled = !c;
	});
}

function cat_staff_disc_value_changed(inp){
	var v = inp.value.trim();
	
	if(v=='')	inp.value='';
	else{
		mf(inp,2);
		v = float(inp.value);
		if(v>100)	inp.value = '100.00';
		else if(v<=0){
			inp.value = 0;
		}
	}
}
function checkall_user(classname,chk) {
	$(document.f_b).getElementsByClassName(classname).each(function(s){
		s.checked = chk;
	});
}

function matrix_changed(){
	var use_matrix = document.f_b['use_matrix'].value;
	if(use_matrix != "yes"){
		$('div_category_matrix').hide();
	}else{
		$('div_category_matrix').show();
	}
}

function category_matrix_override_changed(size){
	var c = $('inp_category_matrix_override-'+size).checked;
	$(document.f_b).getElementsByClassName("category_matrix_min_qty-"+size).each(function(inp){
		inp.readOnly = c;
	});
}

function curtain_clicked2()
{
	$('upload_popup').hide();
	curtain(false);
}

function upload_check()
{
	if (!/\.jpg|\.jpeg/i.test(document.upl.fnew.value))
	{
		alert("Selected file must be a valid JPEG image");
		return false;
	}
	
	return true;
}

function add_image(id)
{
	var obj = $('pos_img');
	if(obj != null){
		alert("Please delete the old image first.");
		return false;
	}

	document.upl.id.value= id;	
	$('upload_popup').show();
	center_div('upload_popup');
	curtain(true);
}

function upload_callback(content)
{
	$('cat_photo').update(content.innerHTML);
	// new Insertion.Bottom($('cat_photo'), content.innerHTML);
	document.upl.fnew.value = '';
	document.f_b.got_pos_photo.value = 1;
	curtain_clicked2();
}

function del_image(obj,fp,id)
{
	ajax_request('masterfile_category.php',
		{
			method: 'get',
			parameters: 'a=ajax_remove_photo&f='+fp+'&id='+id,
			onComplete: function(m) {
				if (m.responseText == 'OK')
				{
					obj.remove();
					document.f_b.got_pos_photo.value = 0;
					document.f_b.has_tmp_photo.value = 0;
					document.f_b.tmp_photo.value = "";
				}
				else
					alert(m.responseText);
			}
		}
	);
}

function set_has_tmp_photo(filepath){
	document.upl.fnew.value = '';
	document.f_b.got_pos_photo.value = 1;
	document.f_b.has_tmp_photo.value = 1;
	document.f_b.tmp_photo.value = filepath;
}

</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">
				Category Master File
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<div class="alert alert-primary rounded mx-3">
	<ul>
		<li> <font color=blue>Level 1 = Line, Level 2 = Department</font></li>
		<li> Click on category to show the sub-categories.</li>
		{if $sessioninfo.privilege.MST_CATEGORY}
		<li> <a href="?a=export_csv">Click Here</a> to download the category as CSV.</li>
			{if $BRANCH_CODE eq 'HQ'}
			<li> <a href="?a=sync">Click Here</a> to regenerate category tree if category is missing from SKU Application.</li>
			<li> <a href="#" onclick="add('0', '(0)', '1');">Click Here</a> to create a new Line.</li>
			<li> To move a category to another parent, click the item's <img src=ui/move.png> icon once, then click on the target parent's <img src=ui/move.png> icon again.</li>
			{/if}
		{/if}
		</ul>
</div>
<div id=category_tree>
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">
					Root Category
				</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width=100%  class="report_table table mb-0 text-md-nowrap  table-hover"
			>
				<thead class="bg-gray-100">
					<table border=0 width=100% cellpadding=0 cellspacing=0>
						<tr bgcolor={#TB_COLHEADER#}>
						<th  rowspan=2 width=60>&nbsp;</th>
						<th rowspan=2 width=50 class="text-center">Code</th>
						<th rowspan=2>Description</th>
						<th rowspan=2 width=50>Area</th>
						<th colspan={count var=$sku_type}>Photos Required for SKU Application</th>
						<th rowspan=2 width=50>GRN PO Qty</th>
						<th rowspan=2 width=50>GRN Weight</th>
						</tr>
						<tr bgcolor={#TB_COLHEADER#} height=20>
						{foreach from=$sku_type item=v key=k}
						<th width=50 class=small>{$k}</th>
						{/foreach}
						</tr>
						<tr>
						<td colspan=9 id="sc0">
						{include file=masterfile_category_table.tpl}
						</td>
						</tr>
						</table>
				</thead>
			<tbody class="fs-08">
				<tr bgcolor={#TB_COLHEADER#} height=20>
					{foreach from=$sku_type item=v key=k}
					<th width=50 class=small>{$k}</th>
					{/foreach}
					</tr>
			</tbody>
				<tr>
				<td colspan=9 id="sc0">
				{include file=masterfile_category_table.tpl}
				</td>
				</tr>
				</table>
		</div>
	</div>
</div>

</div>

<div class="ndiv" id="ndiv" style="position:absolute;left:150;top:50;display:none;z-index:10000;">
<div class="blur"><div class="shadow"><div class="content">

<div class=small style="position:absolute; right:10; text-align:right;"><a href="javascript:void(default_curtain_clicked())" accesskey="C"><img src=ui/closewin.png border=0 align=absmiddle></a><br><u>C</u>lose (Alt+C)</div>
<div id="div_open_cat" style="min-width:400px;min-height:400px;">
	{include file='masterfile_category.open.tpl'}
</div>
</div></div></div>
</div>

<div style="display:none"><iframe name=_irs width=500 height=400 frameborder=1></iframe></div>

<script>
init_chg(document.f_b);
new Draggable('ndiv');
$('photo_tb').style.display = 'none';

</script>
{if $config.ci_auto_gen_artno}
	<script type="text/javascript" src="{$config.ci_auto_gen_artno.js_path}"></script>
{/if}

{include file=footer.tpl}
