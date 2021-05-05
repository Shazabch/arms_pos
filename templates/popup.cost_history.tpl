{*
Revision History
================
18 Apr 2007  - yinsee
- add PO branch column

1/7/2008 10:39:21 AM gary
- add display remark and remark2.

10/15/2012 12:30 PM Andy
- use abs() to trick floating point comparison. (fix mysql -0 problem).

4/17/2018 3:46 PM Andy
- Added Foreign Currency feature.
*}
{if !$po_history}
<h5 align=center>- no history -</h5>
{else}
<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr bgcolor=#EBE8D6>
<th>Date</th>
<th>Qty</th>
<th>FOC</th>
<th>Cost</th>
<th>Selling Price</th>
<th>Tax</th>
<th>Discount</th>
<th>Branch</th>

{section name=i loop=$po_history}
{if $lastvendor != $po_history[i].vendor}
<tr bgcolor="{cycle values="#eeeeee,#ffffff"}">
<td colspan=10>
<h6>{$po_history[i].vendor}</h6>
</td>
</tr>
{assign var=lastvendor value=$po_history[i].vendor}
{/if}

<tr bgcolor="{cycle values="#eeeeee,#ffffff"}">
<td>{$po_history[i].po_date|date_format:"%d/%m/%Y"}</td>
<td align=right>
{if abs($po_history[i].qty)>0}{$po_history[i].qty}x{$po_history[i].uom|default:"PCS"}/{/if}{$po_history[i].qty_loose}
</td>
<td align=right>
{if abs($po_history[i].foc)>0}{$po_history[i].foc}x{$po_history[i].uom|default:"PCS"}/{/if}{$po_history[i].foc_loose}
</td>
<td align="right">
	{if $po_history[i].currency_code}{$po_history[i].currency_code}{/if}
	{$po_history[i].order_price|number_format:$config.global_cost_decimal_points}
	{if $po_history[i].currency_code}
		<br />
		{assign var=base_order_price value=$po_history[i].order_price*$po_history[i].currency_rate}
		<span class="converted_base_amt">{$config.arms_currency.code} {$base_order_price|number_format:$config.global_cost_decimal_points}*</span>
	{/if}
</td>
<td align=right>
{$po_history[i].selling_price|number_format:2}
</td>
<td align=right>
{$po_history[i].tax|number_format:2}
</td>
<td align=right>
{$po_history[i].discount}
</td>
<td align=right>
{if $po_history[i].po_branch_id==0}{$po_history[i].report_prefix}{else}{$po_history[i].report_prefix2}{/if}
</td>

{if $po_history[i].remark}
<tr bgcolor="{cycle values="#eeeeee,#ffffff"}">
<td>&nbsp;</td>
<td colspan=7><img src=ui/note16.png align=absmiddle>{$po_history[i].remark}</td>
</tr>
{/if}

{if $po_history[i].remark2}
<tr bgcolor="{cycle values="#eeeeee,#ffffff"}">
<td>&nbsp;</td>
<td colspan=7><img src=ui/inote16.png align=absmiddle>{$po_history[i].remark2}</td>
</tr>
{/if}

{/section}
</table>
{/if}
