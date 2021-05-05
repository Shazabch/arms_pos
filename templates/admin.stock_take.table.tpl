{*
5/13/2010 2:56:59 PM Andy
- Add column "stock balance" and "variance".

7/23/2010 4:59:47 PM Andy
- Add single server mode and hq can create stock take for branch.
- Fix stock take item list if open multiple tab will cause bugs.

8/19/2010 3:37:37 PM Alex
- edit arrow button same as fresh market stock take module

9/3/2010 2:55:33 PM Alex
- direct add sku items to row

10/8/2010 6:32:31 PM Alex
- add tabindex

7/1/2011 4:59:13 PM Alex
- add show trade discount code for consignment items only

9/27/2011 12:28:11 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

12/2/2011 12:18:32 PM Justin
- Added span onto stock balance display area for qty recalculation purpose.

2/20/2012 3:04:14 PM Alex
- add cost_price column

4/5/2012 10:29:19 AM Alex
- add privilege checking for cost column

2/14/2014 4:43 PM Justin
- Enhanced to calculate and show actual variance at the last item when an item have been insert multiple.
*}

{config_load file=site.conf}
<h5>{count var=$flows} record(s) <span id="span_refreshing"></span></h5>

<table  border=0 cellpadding=4 cellspacing=1>
<tr bgcolor="{#TB_COLHEADER#}">
<th bgcolor="{#TB_CORNER#}" width=40>&nbsp;</th>
<th>Date</th>
<th>Location</th>
<th>Shelf</th>
<th>Username</th>
<th>Arms Code</th>
<th>Mcode</th>
<th>Art No</th>
<th>Description</th>
<th>Price Type</th>
<th>Quantity</th>
{if $sessioninfo.privilege.SHOW_COST}
<th>Unit Cost <b>[<a href="javascript:void(show_cost_help());">?</a>]</b></th>
{/if}
<th>Stock Bal</th>
<th>Variances</th>
</tr>
{if $flows}
{foreach name=f from=$flows item=val}
<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
<td bgcolor={#TB_ROWHEADER#} nowrap>
	<a href="javascript:void(delete_record({$val.id}));"><img src="ui/deact.png" title="Delete" border=0></a>
	<a href="javascript:void(swap('up','{$val.id}','{$val.branch_id}'));" style="{if $smarty.foreach.f.first}visibility:hidden;{/if}">
		<img src="ui/icons/arrow_up.png" title="Swap Up" border=0></a>
	<a href="javascript:void(swap('down','{$val.id}','{$val.branch_id}'));" style="{if $smarty.foreach.f.last}visibility:hidden;{/if}">
		<img src="ui/icons/arrow_down.png" title="Swap Down" border=0></a>
	<input type="hidden" name="mid[{$val.id}]" class="sku_item_id_{$val.sku_item_id}" item_id="{$val.id}" sku_item_id="{$val.sku_item_id}" sb_qty="{$val.sb_qty}" value="{$val.mid}" />
</td>
<td>{$val.date}</td>
<td>{$val.location|upper}</td>
<td>{$val.shelf|upper}</td>
<td>{$val.u|upper}</td>
<td>{$val.sku_item_code}</td>
<td>{$val.mcode}</td>
<td>{$val.artno|upper}</td>
<td>{$val.description|upper}</td>
<td>{$val.trade_discount_code|upper}</td>
<td>
	<input type="text" size=3 name="qtys[{$val.id}]" tabindex='{$smarty.foreach.f.iteration}' value="{$val.qty}" onchange="roundup_value('qty','{$val.doc_allow_decimal}',this); recalc_variance({$val.id}, this.value, {$val.sb_qty|default:0});" style="text-align:right">
</td>
{if $sessioninfo.privilege.SHOW_COST}
<td>
	<input type="text" size=5 name="cost_prices[{$val.id}]" tabindex='{$smarty.foreach.f.iteration}' value="{$val.cost_price}" onchange="roundup_value('cost','{$val.doc_allow_decimal}',this)" style="text-align:right" {if !$sessioninfo.privilege.STOCK_TAKE_EDIT_COST} disabled {/if} >
</td>
{/if}
<td class="r">
	<span id="span_stk_bal_{$val.id}">
		{if $val.mid eq $val.id}
			{if strpos($val.sb_qty,'.')}{$val.sb_qty|qty_nf}{else}{$val.sb_qty|number_format}{/if}
		{else}
			-
		{/if}
	</span>
</td>
{*assign var=variances value=$val.qty-$val.sb_qty*}
{assign var=variances value=$val.variance}
<td class="r {if $variances>0}positive{elseif $variances<0}negative{/if}" id="var_{$val.id}">
	{if $val.mid eq $val.id}
		{if $variances>0}+{/if}{if strpos($variances,'.')}{$variances|qty_nf}{else}{$variances|number_format}{/if}
	{else}
		-
	{/if}
</td>

</tr>
{/foreach}
{/if}
</table>
