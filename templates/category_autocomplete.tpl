{*
11/12/2010 4:44:00 PM Alex
- clear category description if click All

8/15/2011 10:06:33 AM Alex
- add category level control

5/7/2012 10:58:36 AM Andy
- Fix javascript category level checking bugs.

8/9/2017 13:10 PM Qiu Ying
- Enhanced to add category level filter in category autocomplete

3/22/2019 3:31 PM Andy
- Fixed javascript error if no "autocomplete_callback".

8/15/2019 11:31 AM William
- Enhanced to add category level has level 1.
*}

{if !isset($cat_level)}
	{assign var=cat_level value=1}
{/if}

<div class="row">
	<div class="col-md-6">
		<label >Category</label>
	<input readonly id=category_id name=category_id size=1 value="{$smarty.request.category_id}" style="width: 5rem;">
	<input  type=hidden id=category_tree name=category_tree value="{$smarty.request.category_tree}">
	<input class="form-control" id=autocomplete_category name=category value="{$smarty.request.category|default:'Enter keyword to search'}" onfocus="category_onfocus();" size=50 {if $smarty.request.all_category}disabled{/if}>
	{if $all}
	<div class="mt-2">
		<input type="checkbox" id=all_category name="all_category" onChange="all_category_changed(this);" {if $smarty.request.all_category} checked {/if}> <label for='all_category'><b>All</b></label>
	</div>
	{/if}
	</div>
	
	<div class="col-md-6">
		{if !$skip_category_filter}
	<label>Category Level</label>
	<select class="form-control" name="category_level" id="category_level" onchange="category_changed(this);" {if $smarty.request.all_category}disabled{/if}>
	</select>
	{/if}
	</div>
	
</div>
<br><span id=str_cat_tree class=small style="color:#00f;margin-left:90px;">{if !$smarty.request.all_category}{$smarty.request.category_tree|default:''}{/if}</span>
<div id=autocomplete_category_choices class=autocomplete style="width:600px !important"></div>
{literal}
<script>
{/literal}
var cat_min_level=int("{$cat_level}");
var skip_category_filter=int("{$skip_category_filter}");
var tmp_category_level="{$smarty.request.category_level}";
var allow_select_line = int("{$allow_select_line}");
var skip_dept_filter = int("{$skip_dept_filter}");
{literal}

var category_autocompleter = null;
function all_category_changed(e){
	$('category_level').selectedIndex = 0;
	$('category_level').disabled = e.checked;
	$('str_cat_tree').innerHTML='';
	$('category_id').value='';
	$('autocomplete_category').value='';
	$('autocomplete_category').disabled=e.checked;
	{/literal}
	{if $autocomplete_callback}{$autocomplete_callback}{/if}
	{literal}
}

function show_child(id)
{
	// reactivate the auto-completer with child of the category
	setTimeout('category_autocompleter.options.defaultParams = "child='+id+'";category_autocompleter.activate()',250);
}
function init_autocomplete()
{
	if(!skip_category_filter){
		new Ajax.Updater('category_level','ajax_autocomplete.php',
		{
			method:"get",
			evalScripts:true,
			parameters:'a=get_category_level&min_level='+cat_min_level+'&category_level='+tmp_category_level+'&allow_select_line='+allow_select_line,
		});
	}
	
	category_autocompleter = new Ajax.Autocompleter("autocomplete_category", 
	"autocomplete_category_choices", 
	"ajax_autocomplete.php?a=ajax_search_category&skip_dept_filter="+skip_dept_filter+'&min_level='+cat_min_level+'&allow_select_line='+allow_select_line, {
	afterUpdateElement: function (obj,li)
	{
		if(skip_category_filter){
			this.defaultParams = '';
		}
			
		var s = li.title.split(',');
		$('category_id').value = s[0];
		sel_category(obj,s[1]);
		{/literal}
		{if $autocomplete_callback}{$autocomplete_callback}{/if}
		{literal}
	}});
}

function sel_category(obj,have_child)
{
	var str = new String(obj.value);
	str.replace('<span class=sh>', '');
	str.replace('</span>', '');
	$('category_tree').value = str;
	$('str_cat_tree').innerHTML = str;
	obj.value = str.substr(str.lastIndexOf(">")+2, str.length);
}

function category_changed(e){
	$('autocomplete_category').value = "Enter keyword to search";
	$('category_id').value = "";
	$('str_cat_tree').innerHTML = "";
}

function category_onfocus(){
	$("autocomplete_category").select();
	if(!skip_category_filter){
		category_autocompleter.options.defaultParams = "category_level="+$("category_level").value;
	}
}
init_autocomplete();
</script>
{/literal}
