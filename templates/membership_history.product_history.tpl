{*
6/20/2011 4:25:21 PM Andy
- Fix printing membership "Favourite Product" tab bugs. (too many items will jump to second page to start) 
*}

{if $product_history}
<p>* Only the top 100 products will be show</p>
<table width=100%  cellspacing=1 cellpadding=4 border=0>
<thead>
<tr bgcolor=#ffee99>
	<th>{$config.membership_cardname} No</th>
	<th>ARMS Code</th>
	<th>Artno/MCode</th>
	<th>Item Description</th>
	<th width="5%">Total Qty</th>
	<th>Total Price</th>
	<th width="5%">Last Purchase</th>
</tr>
</thead>
<tbody >

{foreach from=$product_history item=h}
{assign var=total_qty value=$total_qty+$h.qty}
{assign var=total_p value=$total_p+$h.price}
<tr bgcolor={cycle values=",#eeeeee"}>
<td>{$h.card_no}</td>
<td>{$h.sku_item_code}</td>
<td>{$h.artno|default:$h.mcode|default:"&nbsp;"}</td>
<td>{$h.receipt_description}</td>
<td align=right>{$h.qty|number_format:2}</td>
<td align=right>{$h.price|number_format:2}</td>
<td align=center nowrap>{$h.dt|default:"&nbsp;"}</td>
</tr>
{/foreach}
</tbody>
<tr bgcolor="#cccccc">
<td colspan=4 align=right><b>Total</b></td>
<td align=right>{$total_qty|number_format:2}</td>
<td align=right>{$total_p|number_format:2}</td>
<td>&nbsp;</td>
</tr>
</table>
{else}
<p align=center>- No History -</p>
{/if}
