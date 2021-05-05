{*
4/22/2015 9:45 AM Justin
- Enhanced to have GST information.

11/30/2015 9:43 PM DingRen
- direct load gst amount from gra item instead of recalculate

2/16/2017 4:21 PM Zhi Kai
-Change wording of 'SKU Item Code' to 'ARMS Code'.
*}

{if $table}
	<tr bgcolor="#afffdd" class="dtl_{$id}_{$bid}">
		<th colspan="2">&nbsp;</th>
		<th>ARMS Code</th>
		<th>MCode</th>
		<th>Article No</th>
		<th>Description</th>
		<th>Qty (Pcs)</th>
		<th>Cost</th>
		<th>Amount</th>
		{if $is_under_gst}
			<th>GST</th>
			<th>Amount<br />Incl. GST</th>
		{/if}
	</tr>
	{foreach from=$table key=r item=gi}
		<tr bgcolor="#eceece" class="dtl_{$id}_{$bid}">
			<th colspan="2">&nbsp;</th>
			<td class="r">{$gi.sku_item_code|default:'-'}</td>
			<td align="center">{$gi.mcode|default:'-'}</td>
			<td align="center">{$gi.artno|default:'-'}</td>
			<td>{$gi.description|default:'-'}</td>
			<td class="r">{$gi.qty|qty_nf}</td>
			<td class="r">{$gi.cost|number_format:$config.global_cost_decimal_points}</td>
			<td class="r">{$gi.amount|number_format:2}</td>
			{if $is_under_gst}
				<td class="r">{$gi.gst|number_format:2}</td>
				<td class="r">{$gi.amount_gst|number_format:2}</td>
			{/if}
		</tr>
	{/foreach}
{else}
	<tr bgcolor="#eceece" class="dtl_{$id}_{$bid}">
		<td align="center" colspan="15">- No data -</td>
	</tr>
{/if}
