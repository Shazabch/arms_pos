<html>
<style>
{literal}
body{
  font-size:9pt;
}

table{
  border-collapse:collapse;
}

table td,table th{
	border: 1px black solid;
	font-size: 11pt;
}

{/literal}
</style>
<body onload="window.print()">
{foreach name=b from=$branches item=branch}
  {foreach name=s from=$sku_items key=page item=si}
    <table width=100% cellpadding=0 cellspacing=0 border=0 style="font-size:11pt;{if !($smarty.foreach.b.last && $smarty.foreach.s.last)}page-break-after:always;{/if}">
    <tr height=24 bgcolor=#ffee99>
      <td colspan="100">Page:&nbsp;{$page+1} of {$total_page}</td>
    </tr>
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
    {foreach from=$si item=i}
    <tr height=24 bgcolor="{cycle name=r1 values=",#eeeeee"}">
      {if $smarty.request.con_split_artno}
        <td nowrap><b>{$i.artno_code}</b></td>
        <td nowrap><b>{$i.artno_size}</b></td>
      {else}
        <td nowrap><b>{$i.artno}</b></td>
      {/if}
      <td nowrap>{$i.description}</td>
      <td align="center">{$i.stock_balance}</td>
      <td align="right">{if $i.selling_price gt 0}{$i.selling_price|number_format:2}{/if}</td>
      {foreach from=$branch item=b}
      <td></td>
      <td></td>
      <td></td>
      {/foreach} 
    </tr>
    {/foreach}
    </table>
  {/foreach}
{/foreach}
</body>
</html>