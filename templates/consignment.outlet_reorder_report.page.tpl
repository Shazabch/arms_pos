{foreach from=$branches item=branch}
<table width=100% cellpadding=2 cellspacing=1 border=0 style="padding:1px;border:1px solid #000;">
<tr height=24 bgcolor=#ffee99>
  {if $smarty.request.con_split_artno}
    <th rowspan="2">Code</th>
    <th rowspan="2">Size</th>
  {else}
    <th rowspan="2">Art No.</th>
  {/if}  
  <th rowspan="2">Item</th>
  <th rowspan="2">BAL</th>
  <th rowspan="2">Price</th>
  {foreach from=$branch item=b}
  <th colspan="3">{$b.code}</th>
  {/foreach}  
</tr>
<tr height=24 bgcolor=#ffee99>
  {foreach from=$branch item=b}
  <th>Code</th>
  <th>BAL</th>
  <th>Order</th>
  {/foreach}  
</tr>
{foreach from=$sku_items item=si}
<tr height=24 bgcolor="{cycle name=r1 values=",#eeeeee"}">
  {if $smarty.request.con_split_artno}
    <td nowrap><b>{$si.artno_code}</b></td>
    <td nowrap><b>{$si.artno_size}</b></td>
  {else}
    <td nowrap><b>{$si.artno}</b></td>
  {/if}
  <td>{$si.description}</td>
  <td align="center">{$si.stock_balance}</td>
  <td align="right">{if $si.selling_price gt 0}{$si.selling_price|number_format:2}{/if}</td>
  {foreach from=$branch item=b}
  <td></td>
  <td></td>
  <td></td>
  {/foreach} 
</tr>
{/foreach}
</table>
{/foreach}