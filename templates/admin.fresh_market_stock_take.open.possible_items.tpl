{*
9/2/2010 5:59:33 PM Andy
- Change condition filter for possible sku.

11/30/2010 5:45:38 PM Andy
- Add load all fresh market items when click "show possible items".
- Add filter for "show possible items" to filter out those already stock taked items.
*}

<form name="f_possible_item" onSubmit="return false;">
	<input type="hidden" name="a" value="load_possible_item_by_condition" />
	<input type="hidden" name="branch_id" value="{$smarty.request.branch_id}" />
	<input type="hidden" name="date" value="{$smarty.request.date|date_format:'%Y-%m-%d'}" />
	<input type="hidden" name="location" value="{$smarty.request.location}" />
	<input type="hidden" name="shelf" value="{$smarty.request.shelf}" />
	
	<b>Filter Conditions</b>
	<select name="condition" onChange="reload_possible_items();">
		<option value="">-- All Fresh Market Items --</option>
		<option value="1">Got GRN since last stock take</option>
		<option value="2">Got Sales since last stock take</option>
		<option value="3">Got Write-off since last stock take</option>
	</select>
	<input type="button" value="Refresh" onClick="reload_possible_items();" />

	<input type="checkbox" name="exclude_stock_taked_items" value="1" /> Exclude stock taked items
	<div id="div_possible_item_list" style="height:400px;border:2px inset #ddd;background:white;overflow-x:hidden;overflow-y:auto;">
		{include file='admin.fresh_market_stock_take.open.possible_items.list.tpl'}
	</div>
</form>

<p align="center">
    <input type="button" value="Add" onClick="add_possible_items();" id="btn_add_possible_item" />
	<input type="button" value="Close" onClick="possible_items_windows_close();" />
</p>

