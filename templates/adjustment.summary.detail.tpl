{*
7/27/2011 5:10:43 PM Justin
- Amended the cost round up to base on config.
- Modified the Ctn and Pcs round up to base on config.

1/29/2015 3:14 PM Andy
- Add Adj In Total Cost & Adj Out Total Cost.

10/13/2015 1:59 PM Andy
- Rename title "Cost" to "AVG Adj Cost".
*}
<div class="noscreen">
<h4>{$p_branch.description}<br>{$title}</h4>
</div>

{if !$items}
-- No Data --
{else}
<table class="report_table" width=100%>
<tr class="header">
	<th rowspan="2">ARMS Code</th>
	<th rowspan="2">Artno</th>
	<th rowspan="2">MCode</th>
	<th rowspan="2">Description</th>
    {if $sessioninfo.privilege.SHOW_COST}
		<th rowspan="2">AVG Adj Cost</th>
	{/if}
	{assign var=cols value=1}
	{if $sessioninfo.privilege.SHOW_COST}{assign var=cols value=$cols+1}{/if}
	<th colspan="{$cols}">Adj IN</th>
	<th colspan="{$cols}">Adj OUT</th>
</tr>
<tr class="header">
	<th>Qty</th>
	{if $sessioninfo.privilege.SHOW_COST}
		<th>Total Cost</th>
	{/if}
	<th>Qty</th>
	{if $sessioninfo.privilege.SHOW_COST}
		<th>Total Cost</th>
	{/if}
<tr>

{assign var=sum_total_in value=0}
{assign var=sum_total_in_cost value=0}
{assign var=sum_total_out value=0}
{assign var=sum_total_out_cost value=0}

{foreach from=$items item=i key=dt}
<tr>
	<td>{$i.info.sku_item_code}</td>
	<td>{$i.info.artno}</td>
	<td>{$i.info.mcode}</td>
	<td>{$i.info.description}</td>
	{if $sessioninfo.privilege.SHOW_COST}
		<td align="right">
			{$i.info.cost_price|number_format:$config.global_cost_decimal_points|ifzero:"-"}
		</td>
	{/if}
	<td align="right">{$i.adj.adj_in|qty_nf|ifempty:"-"}</td>
	{if $sessioninfo.privilege.SHOW_COST}
		<td align="right">{$i.adj.adj_in_cost|number_format:$config.global_cost_decimal_points|ifzero:"-"}</td>
	{/if}
	<td align="right">{$i.adj.adj_out|qty_nf|ifempty:"-"}</td>
	{if $sessioninfo.privilege.SHOW_COST}
		<td align="right">{$i.adj.adj_out_cost|number_format:$config.global_cost_decimal_points|ifzero:"-"}</td>
	{/if}
</tr>
{assign var=sum_total_in value=$sum_total_in+$i.adj.adj_in}
{assign var=sum_total_in_cost value=$sum_total_in_cost+$i.adj.adj_in_cost}

{assign var=sum_total_out value=$sum_total_out+$i.adj.adj_out}
{assign var=sum_total_out_cost value=$sum_total_out_cost+$i.adj.adj_out_cost}
{/foreach}

<tr align="right" bgcolor="#ffee99">
	<th colspan="{if $sessioninfo.privilege.SHOW_COST}5{else}4{/if}">Total</th>
	<th>{$sum_total_in|qty_nf}</th>
	{if $sessioninfo.privilege.SHOW_COST}
		<th>{$sum_total_in_cost|number_format:$config.global_cost_decimal_points|ifzero:"-"}</th>
	{/if}
	<th>{$sum_total_out|qty_nf}</th>
	{if $sessioninfo.privilege.SHOW_COST}
		<th>{$sum_total_out_cost|number_format:$config.global_cost_decimal_points|ifzero:"-"}</th>
	{/if}
</tr>
</table>
{/if}
