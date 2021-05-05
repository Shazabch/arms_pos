{*
1/23/2013 3:48:00 PM Fithri
- add cost & Variance Cost (Cost * Variance Qty) column
*}

{config_load file=site.conf}

<h3>Date : {$stdate}</h3>

<table width="100%" class="report_table" cellpadding="0" cellspacing="0">

<tr class="header">
<th>Arms Code</th>
<th>Mcode</th>
<th>Art No</th>
<th>Description</th>
<th>Category</th>
<th>Cost</th>
<th>Stock Take Qty</th>
<th>Stock Balance</th>
<th>Variance</th>
<th>Variance Cost</th>
</tr>

{if $records}

{assign var=total_qty value=0}
{assign var=total_sb_qty value=0}
{assign var=total_var value=0}
{assign var=total_cost_var value=0}

{foreach name=f from=$records item=val}
<tr>
<td>{$val.sku_item_code}</td>
<td>{$val.mcode|default '&nbsp;'}</td>
<td>{$val.artno|upper|default '&nbsp;'}</td>
<td>{$val.description|upper}</td>
<td>{$val.category|default 'none'}</td>
<td align="right">{$val.cost}</td>
<td align="right">{$val.qty}</td>
<td align="right">{$val.sb_qty}</td>
{assign var=variance value=$val.qty-$val.sb_qty}
{assign var=variance_cost value=$variance*$val.cost}
<td align="right">{$variance}</td>
<td align="right">{$variance_cost}</td>
</tr>

{assign var=total_qty value=$total_qty+$val.qty}
{assign var=total_sb_qty value=$total_sb_qty+$val.sb_qty}
{assign var=total_var value=$total_var+$val.qty-$val.sb_qty}
{assign var=total_cost_var value=$total_cost_var+$variance_cost}

{/foreach}

{/if}

<tr class="header" align="right">
<td colspan="5"><b>Total</b></td>
<td><b>&nbsp;</b></td>
<td><b>{$total_qty}</b></td>
<td><b>{$total_sb_qty}</b></td>
<td><b>{$total_var}</b></td>
<td><b>{$total_cost_var}</b></td>
</tr>

</table>
