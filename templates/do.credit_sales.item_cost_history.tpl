{*
3/18/2010 10:54:32 AM Andy
- Remove the column "Selling Price"
- Change title "Cost" to "Selling Price"
- Add column "UOM"
*}

<h4>{$branch_info.code} - {$debtor_info.code} - {$sku_item_info.sku_item_code}</h4>
<h5>{count var=$items} record{if count($items)>1}s{/if} found.</h5>

<table border=0 cellpadding=4 cellspacing=1 width="100%">
<tr bgcolor="#ebe8d6">
	<th>Date</th>
	<th>DO No.</th>
	<th>Qty</th>
	<th>UOM</th>
	{*<th>Selling Price</th>*}
	<th>DO Price</th>
</tr>

<tbody style="{if count($items)>=13}height:300px;{/if}overflow-x:hidden;overflow-y:auto;">
{foreach from=$items item=r}
	<tr bgcolor="{cycle values='#eeeeee,#ffffff'}" align="center">
	    <td>{$r.do_date}</td>
	    <td>
			<a href="do.php?a=view&id={$r.do_id}&branch_id={$r.branch_id}&highlight_item_id={$r.sku_item_id}" target="_blank">
				{$r.do_no}
			</a>
		</td>
	    <td>{$r.total_qty|number_format}</td>
	    <td>{$r.uom_code}</td>
	    {*<td>{$r.selling_price|number_format:2}</td>*}
	    <td>{$r.cost_price|number_format:3}</td>
	</tr>
{/foreach}
</tbody>
</table>
