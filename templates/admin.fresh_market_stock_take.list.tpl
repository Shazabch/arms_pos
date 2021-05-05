{*
9/1/2010 6:01:24 PM Andy
- Add can direct add stock take item under selected list.

6/22/2011 10:46:34 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.
*}

{config_load file=site.conf}

<h3>
{if $can_select_branch}
	Branch: {$branches[$smarty.request.branch_id].code}
{else}
	{$BRANCH_CODE}
{/if}
&nbsp;&nbsp;&nbsp;&nbsp;
Date: {$smarty.request.date} &nbsp;&nbsp;&nbsp;&nbsp;
Location: {$smarty.request.location} &nbsp;&nbsp;&nbsp;&nbsp;
Shelf: {$smarty.request.shelf} &nbsp;&nbsp;&nbsp;&nbsp;

</h3>
<h5><span id="span_stock_take_list_item_count">{count var=$items}</span> record <span id="span_refreshing"></span></h5>

<form name="f_stock_take_list" method="post" onSubmit="return false;">
<input type="hidden" name="a" />
<input type="hidden" name="branch_id" value="{$smarty.request.branch_id}" />
<input type="hidden" name="date" value="{$smarty.request.date}" />
<input type="hidden" name="location" value="{$smarty.request.location}" />
<input type="hidden" name="shelf" value="{$smarty.request.shelf}" />
<input type="hidden" name="sku_type" value="{$smarty.request.sku_type}" />

<table border="0" cellpadding="4" cellspacing="1">
<tr>
	<th bgcolor="{#TB_CORNER#}" width=40>&nbsp;</th>
	<th bgcolor="{#TB_COLHEADER#}">Date</th>
	<th bgcolor="{#TB_COLHEADER#}">Location</th>
	<th bgcolor="{#TB_COLHEADER#}">Shelf</th>
	<th bgcolor="{#TB_COLHEADER#}">Username</th>
	<th bgcolor="{#TB_COLHEADER#}">Arms Code</th>
	<th bgcolor="{#TB_COLHEADER#}">Mcode</th>
	<th bgcolor="{#TB_COLHEADER#}">Art No</th>
	<th bgcolor="{#TB_COLHEADER#}">Description</th>
	<th bgcolor="{#TB_COLHEADER#}">UOM</th>
	<th bgcolor="{#TB_COLHEADER#}">Quantity</th>
	<th bgcolor="{#TB_COLHEADER#}">Stock Bal</th>
	<th bgcolor="{#TB_COLHEADER#}">Variances</th>
</tr>
	<tbody id="tbody_stock_take_list">
	{foreach from=$items item=r name=f}
	    {include file='admin.fresh_market_stock_take.list.item_row.tpl' item=$r}
	{/foreach}
	</tbody>
</table>

<div id="div_stock_take_direct_add_sku">
<table style="border:1px solid #999; padding:2px; background-color:#dddddd">
	<tr>
	    <td>
	        <input name="inp_autocomplete_sku_item_id" size="3" type="hidden" />
			<b>Search SKU : </b>
	    </td>
	    <td>
	        <input id="inp_autocomplete_sku" name="sku" size=35 onclick="this.select()" />
			<input type="button" value="Add" onclick="add_stock_take_direct_add_item();" style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;">
			<input type="button" value="Multiple Add" onclick="show_stock_take_direct_add_multiple();" style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;">
			<span id="span_stock_take_direct_add_sku_indicator" style="padding:2px;background:yellow;display:none;">
				<img src="ui/clock.gif" align="absmiddle" /> Loading...
			</span>
			<div id="div_autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	    </td>
	</tr>
	<tr>
	    <td></td>
	    <td>
			<input onchange="reset_stock_take_direct_add_sku()" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
			<input onchange="reset_stock_take_direct_add_sku()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
			<input onchange="reset_stock_take_direct_add_sku()" type=radio name="search_type" value="3"> ARMS Code
			<input onchange="reset_stock_take_direct_add_sku()" type=radio name="search_type" value="4"> Description

	    </td>
	</tr>
	<tr>
	    <td>
	        <b>Scan Handheld : </b>
	    </td>
	    <td>
	        <input type="hidden" id="inp_stock_take_direct_add_sid" />
	        <input type="hidden" id="inp_stock_take_direct_add_allow_decimal" />
	         <input id="inp_stock_take_direct_add_handheld_sku" onkeypress="if(event.keyCode==13) search_stock_take_direct_add_handheld_item();" size="35" />
	         <b>&nbsp; Qty: </b>
			 <input id="inp_stock_take_direct_add_handheld_qty" onkeypress="if(event.keyCode==13) add_stock_take_direct_add_handheld_item();" size="5" />
			 <input type="button" value="Add" onclick="add_stock_take_direct_add_handheld_item();" style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;" />
			 <span id="span_stock_take_direct_add_sku_handheld_indicator" style="padding:2px;background:yellow;display:none;">
				<img src="ui/clock.gif" align="absmiddle" /> Loading...
			</span>
	    </td>
	</tr>
</table>
</div>

<p>
	<input type="button" value="Save" onClick="submit_stock_take_list_form('save_stock_list');" />
	<input type="button" value="Delete All" onClick="submit_stock_take_list_form('delete_stock_list');" />
	<input type="button" value="Print Report" onClick="print_stock_report();" />
</p>
</form>
<script>
	stock_take_direct_add_sku_autocomplete = undefined;
	reset_stock_take_direct_add_sku();
</script>
