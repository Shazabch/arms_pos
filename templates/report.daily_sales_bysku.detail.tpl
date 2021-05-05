{foreach from=$data2 key=sku_item_code2 item=d2}
<tr>
<td>{$sku_item_code2}</td>
<td>{$datasub.description}</td>
<td>{$d2.price|ifzero}</td>
{assign var=total value=0}
{foreach from=$alldate item=dt}
{assign var=total value=$total+$d2.$dt}
<td>{$d2.$dt|ifzero}</td>
{/foreach}
<td>{$total|ifzero}</td>
<td>{$total*$d2.price|round2|ifzero}</td>
</tr>
{/foreach}
