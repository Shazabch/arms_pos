<table class="sn_tbl" cellpadding="2" cellspacing="1" width="99%" style="border: 1px solid; padding: 0px;">
<tr bgcolor="#ffeeaa">
	<th width="10%">ARMS Code</th>
	<th width="60%">Description</th>
	<th width="10%">Serial No</th>
	<th width="20%">Remark</th>
</tr>
<tbody {if count($items)>15} style="width:650;height:250;overflow-y:auto;overflow-x:hidden;"{/if}>
{foreach name=i from=$items item=item key=iid}
	<!-- {$n++} -->
	<tr bgcolor="{cycle name=r1 values="#eeeeee,"}" class="no_border_bottom" height="25">
		{if $prev_sku_item_id eq $item.sku_item_id}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{else}
			<td align="center">{$item.sku_item_code|default:"&nbsp;"}</td>
			<td>{$item.sku_description|default:"&nbsp;"}</td>
		{/if}
		<td>{$item.sn}</td>
		<td>{$item.remark|default:"&nbsp;"}</td>
	</tr>
	{assign var=prev_sku_item_id value=$item.sku_item_id}
{/foreach}
{if count($items) eq 0}
	<tr>
		<td align="center" colspan="4" height="25">No data</td>
	</tr>
{/if}
</tbody>
</table>