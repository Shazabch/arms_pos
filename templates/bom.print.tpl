{*
10/10/2011 11:19:42 AM Alex
- add qty_nf control qty decimal point

1/28/2015 3:08 PM Andy
- Change the bom item printing order to same as listing order.

4/21/2017 8:14 AM Khausalya 
- Enhanced changes from RM to use config setting. 
*}
{include file=report_header.potrait.tpl subtitle_r='BOM / Hamper List'}
<br><h1>{$form.description}</h1>
<br>
<table border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>Article No.</b></td>
<td>
{$form.artno|default:"-"}
</td>
</tr>
{if $config.sku_bom_show_mcode}
<tr>
<td><b>Manufacturer's Code</b></td>
<td>
{$form.mcode|default:"-"}
</td>
</tr>
{/if}
<tr>
<td><b>Selling Price</b></td>
<td>{$config.arms_currency.symbol}
{$form.selling_price|number_format:2}
</tr>
</table>

<br><br>

<h4>BOM Content</h4>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">
<thead>
<tr class="hd topline">
	<th>#</th>
	<th nowrap>ARMS Code</th>
	<th nowrap>Article<br>/MCode</th>
	<th nowrap>Description</th>
	<th nowrap width=90>Selling</th>
	<th nowrap width=40>Qty</th>
	<th nowrap width=90>Total Value</th>
</tr>
</thead>
<tr>
<td colspan=7 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>

<tbody>
{assign var=n value=1}
{assign var=total_qty value=0}
{foreach from=$bom_items item=item name=fitem}
<tr>
<td width=10>{$n++}.</td>
<td>{$item.sku_item_code}</td>
<td nowrap>{$item.artno|default:$item.mcode|default:"-"}</td>
<td><div class=crop>{$item.description}</div></td>
{assign var=total_qty value=$item.qty+$total_qty}
{assign var=selling value=$item.qty*$item.price}
{assign var=total_sell value=$total_sell+$selling}
<td align=right>{$item.price|number_format:2}</td>
<td align=center>{$item.qty|qty_nf}</td>
<td align=right>{$selling|number_format:2}</td>
</tr>
{/foreach}
</tbody>

<tfoot>
<tr height=24 bgcolor=#ffffff>
	<td colspan=5 align=right>Total</td>
	<th class="r" id=total_qty>{$total_qty|qty_nf}</th>
	<th class="r" align=right id=total_sell>{$total_sell|number_format:2}</th>
</tr>
</tfoot>

</table>

</div>
