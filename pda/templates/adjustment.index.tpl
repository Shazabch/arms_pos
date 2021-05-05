{*
8/17/2012 9:30 AM Justin
- Bug fixed on adjustment cannot get branch ID.

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

04/11/2020 3:11PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles
	-Remove class small in table and added cellspacing and cellpadding
	-Add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page

11/05/2020 11:48 AM Sheila
- Fixed breadcrumbs

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields

*}
{include file='header.tpl'}

<script>

{literal}
function submit_form(){
	if(document.f_a['adjustment_date'].value==''){
		alert('Please enter Adjustment Date.');
		return false;
	}

	if(document.f_a['adjustment_type'].value==''){
		alert('Please enter Adjustment Type.');
		return false;
	}
	
	if(document.f_a['dept_id'].value==''){
		alert('Please select Department.');
		return false;
	}
	
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

function search_dept(event){
	var k = event.keyCode;
	var desc = $.trim(document.f_a['search_dept_desc'].value).toLowerCase();
	if(desc=='')    return false;

	if(k==13){  // enter
		var opt = undefined;

		var opt_length = document.f_a['dept_id'].length;
		//var i = $(document.f_a['vendor_id']).children();  // get all options
		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			if($(document.f_a['dept_id'].options[i]).text().toLowerCase().indexOf(desc)>=0){ // if found contain the search string
				opt = document.f_a['dept_id'].options[i];   // grap this and break
				break;
			}
		}
		if(opt){ // got row found
            $(opt).attr('selected', true);
		}else{ // no data found
			alert(desc+' not found in Department list');
		}
	}
}

function adj_type_changed(obj){
	if(obj.value != "") document.f_a['adjustment_type'].value = obj.value;
}

{/literal}
</script>
<h1>
Setting - {if $form.id}({$form.report_prefix}{$form.id|string_format:"%05d"}){else}{if $form.id}({$form.report_prefix}{$form.id}){else}New {$module_name}{/if}{/if}
</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a> {if $form.find_adjustment} > <a href="adjustment.php?a=open&find_adjustment={$form.find_adjustment}">Back to search</a> {/if}</span>

<div style="margin-bottom: 10px"></div>

{if $form.id&&$form.branch_id}{include file='adjustment.top_include.tpl'}<br /><br />{/if}



{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}

{if $form.id}
    {assign var=branch_id value=$form.branch_id}
{else}
    {assign var=branch_id value=$sessioninfo.branch_id}
{/if}

<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return false;">
<input type="hidden" name="a" value="save_setting" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="branch_id" value="{$branch_id}" />
<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
	    <th align="left">Date</th>
	    <td>
			<input type="text" id="inp_date" name="adjustment_date" value="{$form.adjustment_date|default:$smarty.now|date_format:'%Y-%m-%d'}" size="10" /> <span class="small"> (YYYY-MM-DD) </span>
		</td>
	</tr>
	<tr>
	    <th align="left">Type</th>
	    <td><input type="text" size="30" name="adjustment_type" value="{$form.adjustment_type}" /></td>
	</tr>
	<tr>
	    <th align="left">Preset Type</th>
	    <td>
	        <select name="preset_type" onChange="adj_type_changed(this);">
	            <option value="">--</option>
	            {foreach from=$config.adjustment_type_list item=type_item}
	                <option value="{$type_item.name|upper}" {if $form.adjustment_type eq $type_item.name|upper}selected {/if}>{$type_item.name|upper}</option>
	            {/foreach}
	        </select>
		</td>
	</tr>
    <tr>
	    <th align="left">Department</th>
	    <td>
	        <select name="dept_id">
	            <option value="">-- Please Select --</option>
	            {foreach from=$dept key=r item=d}
	                <option value="{$d.id}" {if $form.dept_id eq $d.id}selected {/if}>{$d.description}</option>
	            {/foreach}
	        </select>
	    </td>
	</tr>
	<tr>
	    <th align="left">Search Dept</th>
	    <td><input type="text" name="search_dept_desc" onKeyUp="search_dept(event);" /></td>
	</tr>
	<tr>
	    <th align="left">Remark</th>
	    <td><textarea name="remark">{$form.remark}</textarea></td>
	</tr>
	<tr>
	    <th align="left">Branch</th>
	    <td>
			{if $form.id}
				{assign var=bid value=$form.branch_id}
				{$form.branch_code} - {$form.description}
			{else}
				{if $BRANCH_CODE eq "HQ"}
					<select name="branch_id">
						{foreach from=$branches key=bid item=b}
							<option value="{$bid}">{$b.code}</option>
						{/foreach}
					</select>
				{else}
					{assign var=bid value=$sessioninfo.branch_id}
					{$branches.$bid.code}
				{/if}
			{/if}
		</td>
	</tr>
</table>
<p align="center">
	<input type="button" name="submit_btn" value="Save" onClick="submit_form();" />
</p>
</form>
</div>
{include file='footer.tpl'}
