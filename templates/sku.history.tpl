{*
6/22/2011 11:10:26 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.
*}

{if !$smarty.request.ajax}
{include file=header.tpl}

{literal}
<script>
// update autocompleter parameters when vendor_id or department_id changed
var sku_autocomplete = undefined;

function reset_sku_autocomplete()
{
	//var param_str = "a=ajax_search_sku&dept_id={/literal}{$form.department_id}{literal}&type="+getRadioValue(document.f_a.search_type);
	var param_str = "a=ajax_search_sku&type="+getRadioValue(document.f_a.search_type);
	if (sku_autocomplete != undefined)
	{
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else
	{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_a.sku_item_id.value = s[0];
			document.f_a.sku_item_code.value = s[1];
			
		}});
	}
	clear_autocomplete();
}

function clear_autocomplete(){
	document.f_a.sku_item_id.value = '';
	document.f_a.sku_item_code.value = '';
	$('autocomplete_sku').value = '';
	$('autocomplete_sku').focus();
}
</script>
{/literal}

<h1>SKU History</h1>

<form name=f_a method=post>
<input type=hidden name=a value=find>
<table class=tl>
{if $branch}
	<tr>
	<th>Branch</th>
	<td>
	<select name=branch_id>
	{foreach from=$branch item=b}
	<option value="{$b.id}" {if $smarty.request.branch_id == $b.id}selected{/if}>{$b.code}</option>
	{/foreach}
	</select>
	</td>
	</tr>
{/if}
	<tr>
	<th>Search SKU</th>
	<td>
		<input name="sku_item_id" size=3 type=hidden value="{$smarty.request.sku_item_id}">
		<input name="sku_item_code" size=13 type=hidden value="{$smarty.request.sku_item_code}">
		<input id="autocomplete_sku" name="sku" size=40 onclick="this.select()" style="font-size:14px;width:500px;" value="{$smarty.request.sku}">
		<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	</td>
	<td><input type=submit value="Find"></td>
	</tr><tr>
	<td>&nbsp;</td>
	<td>
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="3"> ARMS Code
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="4"> Description
	</td>
</tr>
</table>
</form>

{/if}

{if $smarty.request.sku_item_id or $smarty.request.sku_id}
<h2>{$smarty.request.sku_item_code}: {$smarty.request.sku}</h2>

{if $branches_balance}
Click on Branch to view detail
<table class=tb cellpadding=2 cellspacing=0 width=500>
<tr bgcolor=#ffee99><th>Branch</th><th>GRN</th><th>GRA</th><th>POS</th><th>Adjust</th><th>Balance</th></tr>
{section loop=$branches_balance name=i}
<tr>
	<td>{if !$smarty.request.ajax}
	<a href="?branch_id={$branches_balance[i].branch_id}{if $smarty.request.sku_item_id}&sku_item_id={$smarty.request.sku_item_id}{/if}{if $smarty.request.sku_id}&sku_id={$smarty.request.sku_id}{/if}"><b>{$branches_balance[i].branch}</b></a>
	{else}
	<a href="javascript:void(show_inventory({if $smarty.request.sku_item_id}'sku_item_id',{$smarty.request.sku_item_id}{/if}{if $smarty.request.sku_id}'sku_id',{$smarty.request.sku_id}{/if},{$branches_balance[i].branch_id}))"><b>{$branches_balance[i].branch}</b></a>
	{/if}
	</td>
	<td class=r>{$branches_balance[i].grn|ifzero:"&nbsp;"}</td>
	<td class=r>{$branches_balance[i].gra|ifzero:"&nbsp;"}</td>
	<td class=r>{$branches_balance[i].pos|ifzero:"&nbsp;"}</td>
	<td class=r>{$branches_balance[i].adjust|ifzero:"&nbsp;"}</td>
	<td class=r>{$branches_balance[i].total|ifzero:"&nbsp;"}</td>
</tr>
{/section}
</table>

{else}

{if $history}
<h3>Inventory Balance: {$balance.total}</h3> 

<table class=tb cellpadding=2 cellspacing=0 width=500>
<tr bgcolor=#ffee99><th>Date</th><th>GRN</th><th>GRN<br>Cost</th><th>GRA</th><th>POS</th><th>Selling<br>Price</th><th>Adjust</th><th>Balance</th>{if $smarty.request.ajax}<td width=20>&nbsp;</td>{/if}</tr>
<tbody{if $smarty.request.ajax} style="height:320px;overflow:auto;"{/if}>
{section loop=$history name=i}
{assign var=bal value=$bal+$history[i].grn-$history[i].gra-$history[i].pos+$history[i].adjustment}
<tr bgcolor="{cycle values=",#eeeeee"}">
	<td>{$history[i].date}</td>
	<td>{$history[i].grn|ifzero:"&nbsp;"}</td>
	<td>{$history[i].cost|number_format:3|ifzero:"&nbsp;"}</td>
	<td>{$history[i].gra|ifzero:"&nbsp;"}</td>
	<td>{$history[i].pos|ifzero:"&nbsp;"}</td>
	<td>{$history[i].selling|number_format:2|ifzero:"&nbsp;"}</td>
	<td>{$history[i].adjustment|ifzero:"&nbsp;"}</td>
	<td>{$bal}</td>
	{if $smarty.request.ajax}<td>&nbsp;</td>{/if}
</tr>
{/section}
</tbody>
</table>

{else}
- no inventory information for this item in selected branch -
{/if}
{/if}
{/if}

{if !$smarty.request.ajax}
{include file=footer.tpl}
{/if}
