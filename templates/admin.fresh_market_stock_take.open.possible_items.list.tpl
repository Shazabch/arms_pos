{*
9/28/2011 10:55:45 AM Justin
- Modified the Ctn and Pcs round up to base on config set.
*}

<table width="100%" style="border-collapse:collapse;" border="1">
	<tr bgcolor="#cccccc">
	    <th rowspan="2" width="50"></th>
		<th rowspan="2" width="100">ARMS Code</th>
		<th rowspan="2">Description</th>
		<th rowspan="2" width="60">UOM</th>
		<th colspan="2" width="120">Last Stock Take</th>
	</tr>
	<tr bgcolor="#cccccc">
	    <th>Date</th>
	    <th>Qty</th>
	</tr>
	{foreach from=$items item=r}
	    <tr>
	        <td><input type="checkbox" name="sid[]" value="{$r.sku_item_id}" /></td>
	        <td>{$r.sku_item_code}</td>
	        <td>{$r.description}</td>
	        <td>{$r.uom_code}</td>
	        <td align="center">{$r.sc_date|default:'-'}</td>
	        <td class="r">{$r.sc_qty|qty_nf}</td>
	    </tr>
	{/foreach}
</table>
