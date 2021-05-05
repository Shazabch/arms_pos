{*

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

04/11/2020 10:40AM Rayleen
- Modified page style/layout. 
	-Add h1/h3 in titles
	-Remove class small in table and added cellspacing and cellpadding 

11/04/2020 3:08PM Rayleen
- Modified page style/layout. 
	-Add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}
{include file='header.tpl'}

<script>

{literal}
function submit_form(){

	if(document.f_a['vendor_id'].value==''){
		alert('Please select Vendor.');
		return false;
	}
	
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

function search_vendor(event){
	var k = event.keyCode;
	var desc = $.trim(document.f_a['search_vendor_desc'].value).toLowerCase();
	if(desc=='')    return false;

	if(k==13){  // enter
		var opt = undefined;

		var opt_length = document.f_a['vendor_id'].length;
		//var i = $(document.f_a['vendor_id']).children();  // get all options
		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			if($(document.f_a['vendor_id'].options[i]).text().toLowerCase().indexOf(desc)>=0){ // if found contain the search string
				opt = document.f_a['vendor_id'].options[i];   // grap this and break
				break;
			}
		}
		if(opt){ // got row found
            $(opt).attr('selected', true);
		}else{ // no data found
			alert(desc+' not found in vendor list');
		}
	}
}
{/literal}
</script>
<h1>
Setting - {if $form.gra_no}(GRA/{$form.gra_no}){else}{if $form.id}(GRA#{$form.id}){else}New GRA{/if}{/if}
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a></span>
<div style="margin-bottom:10px;"></div>

{if $form.id&&$form.branch_id}{include file='goods_return_advice.top_include.tpl'}<br /><br />{/if}

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
	    <th align="left">Vendor</th>
	    <td>
	        <select name="vendor_id">
	            <option value="">-- Please Select --</option>
	            {foreach from=$vendors key=vid item=r}
	                <option value="{$vid}" {if $form.vendor_id eq $vid}selected {/if}>{$r.code} - {$r.description}</option>
	            {/foreach}
	        </select>
	    </td>
	</tr>
	<tr>
	    <th align="left">Search Vendor</th>
	    <td><input type="text" name="search_vendor_desc" onKeyUp="search_vendor(event);" />
	</tr>
    <tr>
	    <th align="left">SKU Type</th>
	    <td>
	        <select name="sku_type">
	            <option value="">-- Please Select --</option>
	                <option value="OUTRIGHT" {if $form.sku_type eq 'OUTRIGHT'}selected {/if}>Outright</option>
	                <option value="CONSIGN" {if $form.sku_type eq 'CONSIGN'}selected {/if}>Consignment</option>
	        </select>
	    </td>
	</tr>
    <tr>
	    <th align="left">Department</th>
	    <td>
	        <select name="dept_id">
	            <option value="">-- Please Select --</option>
	            {foreach from=$departments key=did item=r}
	                <option value="{$did}" {if $form.dept_id eq $did}selected {/if}>{$r.description}</option>
	            {/foreach}
	        </select>
	    </td>
	</tr>
</table>
<p align="center">
	<input type="button" name="submit_btn" value="Save" onClick="submit_form();" />
</p>
</form>
</div>
{include file='footer.tpl'}
