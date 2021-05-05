{*
10/14/2011 12:06:19 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

*}
{foreach from=$data2 key=sku_item_code2 item=d2}
<tr>
<td align=center>{$sku_item_code2}</td>
<td>{$datasub.description}</td>
<td align=right>{$d2.price|ifzero}</td>
{assign var=total value=0}
{foreach from=$alldate item=dt}
{assign var=total value=$total+$d2.$dt}
<td align=center>{$d2.$dt|qty_nf|ifzero}</td>
{/foreach}
<td align=center>{$total|qty_nf|ifzero}</td>
<td align=right>{$total*$d2.price|round2|ifzero}</td>
</tr>
{/foreach}
