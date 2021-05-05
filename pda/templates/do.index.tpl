{*
10/11/2011 10:42:12 AM Justin
- Added the search debtor and branch features.
- Re-aligned the form layout to fill under PDA screen.

7/31/2012 10:28:34 AM Justin
- Bug fixed of user can choose to deliver same branch as branch deliver.

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

10/29/2020 5:00 PM Sheila
- Fixed title, table and form css

11/04/2020 4:30 PM Rayleen
- Modified title, add submenu in breadcrumbs (Dashboard>SubMenu)

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields

*}

{include file='header.tpl'}

<script>
var do_type = '{$do_type}';
var do_branch_id = "";
{if $form.id}
	var branch_id = '{$form.branch_id}';
	var do_branch_id = '{$form.do_branch_id}';
{else}
	var branch_id = '{$sessioninfo.branch_id}';
{/if}

{literal}
function submit_form(){
	if(document.f_a['do_date'].value==''){
		alert('Please enter DO Date.');
		return false;
	}

	if(do_type=='open'){
	    if(document.f_a['open_info[name]'].value==''){
			alert('Please key in Company Name');
			return false;
		}
		if(document.f_a['open_info[address]'].value==''){
            alert('Please key in Address');
			return false;
		}
	}else if(do_type=='credit_sales'){
        if(document.f_a['debtor_id'].value==''){
			alert('Please select Debtor.');
			return false;
		}
	}else{  // transfer
        if(document.f_a['do_branch_id'].value==''){
			alert('Please select Deliver To Branch.');
			return false;
		}
	}
	
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

function search_debtor(event){
	var k = event.keyCode;
	var desc = $.trim(document.f_a['search_debtor_desc'].value).toLowerCase();
	if(desc=='')    return false;

	if(k==13){  // enter
		var opt = undefined;

		var opt_length = document.f_a['debtor_id'].length;
		//var i = $(document.f_a['vendor_id']).children();  // get all options
		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			if($(document.f_a['debtor_id'].options[i]).text().toLowerCase().indexOf(desc)>=0){ // if found contain the search string
				opt = document.f_a['debtor_id'].options[i];   // grap this and break
				break;
			}
		}
		if(opt){ // got row found
            $(opt).attr('selected', true);
		}else{ // no data found
			alert(desc+' not found in Debtor list');
		}
	}
}

function search_branch(event){
	var k = event.keyCode;
	var desc = $.trim(document.f_a['search_branch_desc'].value).toLowerCase();
	if(desc=='')    return false;

	if(k==13){  // enter
		var opt = undefined;

		var opt_length = document.f_a['do_branch_id'].length;
		//var i = $(document.f_a['vendor_id']).children();  // get all options
		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			if($(document.f_a['do_branch_id'].options[i]).text().toLowerCase().indexOf(desc)>=0){ // if found contain the search string
				opt = document.f_a['do_branch_id'].options[i];   // grap this and break
				break;
			}
		}
		if(opt){ // got row found
            $(opt).attr('selected', true);
		}else{ // no data found
			alert(desc+' not found in Branch list');
		}
	}
}

function branch_check(obj){
	if(obj.value == branch_id){
		alert("Cannot deliver to same branch!");
		obj.value = do_branch_id;
	}
}
{/literal}
</script>
<h1>
{if $do_type eq 'open'}Cash Sales
{elseif $do_type eq 'credit_sales'}Credit Sales
{else}Transfer {/if} DO
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard </a> > <a href="home.php?a=menu&id=do">DO</a></span>
<div style="margin-bottom:10px;"></div>
{if $form.id&&$form.branch_id}{include file='do.top_include.tpl'}<br /><br />{/if}

<h3>Setting - {if $form.do_no}(DO/{$form.do_no}){else}{if $form.id}(DO#{$form.id}){else}New DO{/if}{/if}</h3>

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
<input type="hidden" name="do_type" value="{$do_type}" />

<table cellspacing="0" cellpadding="4" border="0" width="100%">
	<tr>
	    <th align="left">DO Date</th>
	    <td>
			<input type="text" id="inp_do_date" name="do_date" value="{$form.do_date|default:$smarty.now|date_format:"%Y-%m-%d"}" size="10" /> <span class="small">(YYYY-MM-DD)</span>
		</td>
	</tr>
	<tr>
	    <th align="left">Deliver From</th>
	    <td>
			{$branches.$branch_id.code} - {$branches.$branch_id.description}
		</td>
	</tr>
	{if $do_type eq 'open'}
	    <tr>
	        <th align="left" valign="top">Deliver To</th>
     		<td>
     		    <table width="100%">
     		        <tr>
	     		        <td>Company Name</td>
					</tr>
					<tr>
	     		        <td><input onchange="this.value=this.value.toUpperCase();" value="{$form.open_info.name}" name="open_info[name]" class="txt-width" /></td>
     		        </tr>
     		        <tr>
     		            <td align="left">Address</td>
					</tr>
					<tr>
     	    			<td><textarea name="open_info[address]" cols="15">{$form.open_info.address}</textarea>
     		        </tr>
     		    </table>
				
			</td>
     	</tr>
	{elseif $do_type eq 'credit_sales'}
	    <tr>
	        <th align="left">Deliver To</th>
	        <td>Debtor</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<select name="debtor_id">
					<option value="">-- Please Select --</option>
					{foreach from=$debtors item=r}
						<option value="{$r.id}" {if $form.debtor_id eq $r.id}selected {/if}>{$r.code}</option>
					{/foreach}
				</select>
			</td>
	    </tr>
		<tr>
			<th align="left">Search Debtor</th>
			<td><input type="text" name="search_debtor_desc" onKeyUp="search_debtor(event);" />
		</tr>
	{else}
		<tr>
		    <th align="left">Deliver To</th>
		    <td>
		        <select name="do_branch_id" onChange="branch_check(this);">
		            <option value="">-- Please Select --</option>
		            {foreach from=$branches_group.header key=bgid item=bg}
		                <optgroup label="{$bg.code}">
		                    {foreach from=$branches_group.items.$bgid key=bid item=b}
		                        <option value="{$bid}" {if $form.do_branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
		                    {/foreach}
		                </optgroup>
		            {/foreach}
		            {foreach from=$branches key=bid item=b}
		                {if !$branches_group.have_group.$bid}
		                    <option value="{$bid}" {if $form.do_branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
		                {/if}
		            {/foreach}
		        </select>
		    </td>
			<tr>
				<th align="left">Search Branch</th>
				<td><input type="text" name="search_branch_desc" onKeyUp="search_branch(event);" />
			</tr>
		</tr>
	{/if}
</table>
<p align="center">
	<input name="submit_btn" type="button" value="Save" onClick="submit_form();" />
</p>
</form>
</div>

{include file='footer.tpl'}
