{*
1/9/2013 2:17:00 PM Fithri
- rewrite the code, change JS and PHP to object-oriented

1/16/2013 9:39:00 AM Fithri
- import button change to "confirm stock take"
- zerolise checkbox change to "auto fill zero quantity for unfilled items"
- add column to show items category (level 3)
- can sort by category or description
- add print button to print out the item list

1/18/2013 2:35 PM Justin
- Bug fixed on date and zerolize checkbox do not auto fill up once do sorting.

1/23/2013 10:00:00 AM Fithri
- add stock balance, variance column

1/23/2013 3:48:00 PM Fithri
- stock balance & variance dont show in input, direct show it using span
- add Variance Cost (Cost * Variance Qty) column
*}

{config_load file=site.conf}
<h5>{count var=$flows} record(s) <span id="span_refreshing"></span></h5>

Date:&nbsp;&nbsp;<input type="text" name="stock_take_date" id="stock_take_date" size="10" value="{if $stock_take_date}{$stock_take_date}{else}{$smarty.request.date}{/if}" {if !$smarty.request.new_stock_take}readonly{/if} />
{if $smarty.request.new_stock_take}<img align=absmiddle src="ui/calendar.gif" name="stock_take_date_btn" id="stock_take_date_btn" style="cursor: pointer;" title="Select Date" />{/if}
&nbsp;&nbsp;
{*<label><input name="zerolize" type="checkbox" value="Zerolize" {if $zerolize}checked{/if} />Autofill zero quantity for unfilled item(s)</label>*}
<br /><br />

{if $new_stock_take_remark}<span style="color:#f00;"> * </span>You must click 'Save' button first to see stock balance<br /><br />{/if}

<table border=0 cellpadding=4 cellspacing=1>
<tr bgcolor="{#TB_COLHEADER#}">
<th>Arms Code</th>
<th>Mcode</th>
<th>Art No</th>
<th>Description</th>
<th>Category</th>
<th>Cost</th>
<th>Quantity</th>
<th>Stock Balance</th>
<th>Variance</th>
<th>Variance Cost</th>
</tr>
{if $flows}
{foreach name=f from=$flows item=val}
<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';" class="stock_take_item" id="strow_{$val.id}">
<td>{$val.sku_item_code}</td>
<td>{$val.mcode}</td>
<td>{$val.artno|upper}</td>
<td>{$val.description|upper}</td>
<td>{$val.category}</td>
<td align="right">{$val.cost_price}</td>

{if $currqtys}
{assign var=qty value=$currqtys[$val.id]}
{else}
{assign var=qty value=$val.qty}
{/if}

<td>
	<input type="text" size=3 name="qtys[{$val.id}]" id="qty_{$val.id}" tabindex='{$smarty.foreach.f.iteration}' value="{$qty}" onchange="STOCKTAKE.btn_import(false);STOCKTAKE.roundup_value('{$val.doc_allow_decimal}',this,{$val.id});" style="text-align:right" />
	<input type="hidden" id="cost_{$val.id}" name="cost_prices[{$val.id}]" value="{$val.cost_price}" />
	<input type="hidden" name="is_new_item[{$val.id}]" value="{$val.new_item}" />
</td>
<td align="right">
	<span id="span_sb_qty_{$val.id}">{$val.sb_qty}</span>
	<input type="hidden" id="sb_qty_{$val.id}" value="{$val.sb_qty}"/>
</td>
<td align="right">
	{if $qty or $qty eq '0'}
	{assign var=variance value=$qty-$val.sb_qty}
	{else}
	{assign var=variance value=''}
	{/if}
	<span id="span_var_{$val.id}">{$variance}</span>
	<input type="hidden" id="var_{$val.id}" value="{$variance}"/>
</td>
<td align="right">
	<span id="span_var_cost_{$val.id}">{$val.cost_price*$variance}</span>
</td>
</tr>
{/foreach}
{/if}
</table>
