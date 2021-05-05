{*
3/29/2011 5:08:14 PM Andy
- Add can search debtor.

10/3/2011 5:11:43 PM Justin
- Fixed the search vendor not working properly.

10/11/2011 11:50:11 AM Justin
- Reduced the width of Debtor drop down list.

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

04/11/2020 3:24PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu), then link to module menu page
	-Remove class small in table and added cellspacing and cellpadding

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}
{include file='header.tpl'}

<script>

{literal}
function submit_form(){
	if(document.f_a['order_date'].value==''){
		alert('Please enter Order Date.');
		return false;
	}

	if(document.f_a['debtor_id'].value==''){
		alert('Please select To Debtor.');
		return false;
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
{/literal}
</script>
<h1>
Setting - {if $form.order_no}(SO/{$form.order_no}){else}{if $form.id}(SO#{$form.id}){else}New SO{/if}{/if}
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$module_name}</a></span>
<div style="margin-bottom:10px;"></div>

{if $form.id&&$form.branch_id}{include file='sales_order.top_include.tpl'}<br /><br />{/if}


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
	    <th align="left">Order Date</th>
	    <td>
			<input type="text" id="inp_date" name="order_date" value="{$form.order_date|default:$smarty.now|date_format:"%Y-%m-%d"}" size="10" /> <span class="small"> (YYYY-MM-DD) </span>
		</td>
	</tr>
	<tr>
	    <th align="left">From</th>
	    <td>
			{$branches.$branch_id.code} - {$branches.$branch_id.description}
		</td>
	</tr>
	<tr>
	    <th align="left">Batch Code</th>
	    <td><input type="text" size="15" name="batch_code" value="{$form.batch_code}" />
	</tr>
	<tr>
	    <th align="left">Customer PO</th>
	    <td><input type="text" size="15" name="cust_po" value="{$form.cust_po}" />
	</tr>
    <tr>
	    <th align="left">To Debtor</th>
	    <td>
	        <select name="debtor_id">
	            <option value="">-- Please Select --</option>
	            {foreach from=$debtors key=did item=r}
	                <option value="{$did}" {if $form.debtor_id eq $did}selected {/if}>{$r.code} - {$r.description}</option>
	            {/foreach}
	        </select>
	    </td>
	</tr>
	<tr>
	    <th align="left">Search Debtor</th>
	    <td><input type="text" name="search_debtor_desc" onKeyUp="search_debtor(event);" />
	</tr>
</table>
<p align="center">
	<input type="button" name="submit_btn" value="Save" onClick="submit_form();" />
</p>
</form>
</div>
{include file='footer.tpl'}
