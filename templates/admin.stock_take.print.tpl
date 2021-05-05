{*
5/13/2010 2:57:14 PM Andy
- Add column "stock balance" and "variance".

5/18/2010 5:48:09 PM Andy
- Stock Take Printing now separate by shelf.

9/2/2010 3:45:37 PM Andy
- Print report add uom.

9/23/2010 11:13:00 AM Andy
- Add cost at stock take printing. (need config and privilege)

7/14/2011 12:44:37 PM Andy
- Add price type to stock take print sheet.

7/15/2011 11:22:51 AM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

9/23/2011 10:55:45 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

2/14/2014 4:43 PM Justin
- Enhanced to calculate and show actual variance at the last item when an item have been insert multiple.

4/25/2017 1:52 PM Khausalya
- Enhanced changes from RM to use config setting. 

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
*}

{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>

{literal}
td div.crop{
  height:auto;
  max-height:2em;
  overflow:hidden;
}
{/literal}
</style>
<body onload="window.print()">
{/if}


<div class=printarea>
<h3>{$branch.description|upper} - {$branch.code} {if $branch.company_no}({$branch.company_no}){/if}</h3>

<table>
<tr>
	<td nowrap><b>Stock Take Date</b>:</td><td width=80 style="border-bottom:1px solid #000;">{$form.date}</td>
	<td nowrap><b>Print Date</b>:</td><td width=80 style="border-bottom:1px solid #000;">{$smarty.now|date_format:"%Y-%m-%d"}</td>
	<td nowrap><b>Location</b>:</td><td width=130 style="border-bottom:1px solid #000;">{$form.location|upper}</td>
	<td nowrap><b>Shelf</b>:</td><td width=130 style="border-bottom:1px solid #000;">{$form.shelf|upper}</td>
	{if $stock_count_sheet}
		<td nowrap><b>Stock Count Sheet No.</b>:</td>
		<td width=130 style="border-bottom:1px solid #000;">{$stock_count_sheet|upper}</td>
	{/if}
</tr>
<tr>
	<td nowrap><b> Create First/Last</b>:</td><td width=80 style="border-bottom:1px solid #000;">
	{foreach from=$user_create item=r name=foo}
	{$r|upper}{if Not $smarty.foreach.foo.last},{/if}
	{/foreach}
	</td>
	<td nowrap><b>Count By</b>:</td><td width=80 style="border-bottom:1px solid #000;">&nbsp;</td>
	<td nowrap><b>Verify By</b>:</td><td width=130 style="border-bottom:1px solid #000;">&nbsp;</td>

	<td nowrap><b>Printed By</b>:</td><td width=130 style="border-bottom:1px solid #000;">{$user_name|upper}</td>
	<td nowrap><b>Audit By</b>:</td><td width=130 style="border-bottom:1px solid #000;">&nbsp;</td>
</tr>
</table>
{$page}
<br>
<table  border=0 cellpadding=2 cellspacing=0 align=center width=100% class="tb">
<tr>
<th align=center bgcolor="{#TB_COLHEADER#}">Item</th>
<th align=center bgcolor="{#TB_COLHEADER#}" width=40>Arms Code/<br>Mcode</th>
<th align=center bgcolor="{#TB_COLHEADER#}" width=80>Art No/<br>Old Code</th>
<th align=center bgcolor="{#TB_COLHEADER#}">Description</th>
<th align=center bgcolor="{#TB_COLHEADER#}" width=50>UOM</th>
{if $config.stock_take_printing_show_cost and $sessioninfo.privilege.SHOW_COST}
    <th align="center" bgcolor="{#TB_COLHEADER#}" width="50">Cost</th>
{/if}
<th align=center bgcolor="{#TB_COLHEADER#}" width=50>Selling<br>Price({$config.arms_currency.symbol})</th>
<th align=center bgcolor="{#TB_COLHEADER#}" width=50>Price Type</th>
<th align=center bgcolor="{#TB_COLHEADER#}" width=60>Qty</th>
{if !$sheet}
    <th align=center bgcolor="{#TB_COLHEADER#}" width=60>Stock<br />Bal</th>
    <th align=center bgcolor="{#TB_COLHEADER#}" width=60>Variances</th>
{/if}
</tr>

{assign var=counter value=0}

{foreach from=$items item=r}
    <!-- {$counter++} -->
    <tr height="35">
		<td align=center width="20">{$start_counter+$counter}.</td>
		<td width="40">{if $r.sku_item_code}{$r.sku_item_code}/<br>{$r.mcode}{else}&nbsp;{/if}</td>
		<td width=80>{if $r.artno}{$r.artno}/<br>{$r.link_code}{else}&nbsp;{/if}</td>
		<td><div class="crop">{$r.description|default:'&nbsp;'}</div></td>
		<td align="center" width=50>{$r.uom_code|default:'&nbsp;'}</td>
		{if $config.stock_take_printing_show_cost and $sessioninfo.privilege.SHOW_COST}
		    <td align="right" width=50>{$r.cost|number_format:$config.global_cost_decimal_points|default:'&nbsp;'}</td>
		{/if}
		<td align=right width=50>{if $r.selling_price}{$r.selling_price|number_format:2}{else}&nbsp;{/if}</td>
		<td align="center">{$r.trade_discount_code|default:'&nbsp;'}</td>
		<td width=40 align=right>
			{if !$sheet}
			    {$r.qty|qty_nf|default:'&nbsp;'}
			{else}
			    {if $smarty.request.print_with_qty}{$r.qty|qty_nf|default:'&nbsp;'}{else}&nbsp;{/if}
			{/if}
		</td>
		{if !$sheet}
			<td align="right">
				{if $r.mid eq $r.id}
					{$r.sb_qty|qty_nf|default:'&nbsp;'}
				{else}
					-
				{/if}
			</td>
			<td align="right">
			{if $r.sku_item_code}
				{if $r.mid eq $r.id}
					{assign var=variances value=$r.variance}
					{if $variances>0}+{/if}{$variances|qty_nf|default:'&nbsp;'}
				{else}
					-
				{/if}
			{else}&nbsp;{/if}
			</td>
		{/if}
	</tr>
{/foreach}


{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height="35">
  	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	
	{if $config.stock_take_printing_show_cost and $sessioninfo.privilege.SHOW_COST}
	    <td>&nbsp;</td>
	{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	
    {if !$sheet}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/section}
</table>
</div>
