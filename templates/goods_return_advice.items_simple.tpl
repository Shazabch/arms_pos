{*
Revision History
================

7/6/2010 4:36:48 PM Justin
- Added Selling Price column.

4/29/2011 4:21:11 PM Justin
- Rounding is now base on config['gra_cost_decimal_points'], if not found means all round by 2 decimal points.

10/19/2012 12:19 PM Justin
- Enhanced to disable general information while found user assigned items to the following GRA.

2/19/2014 4:54 PM Justin
- Enhanced to always allow user to remove item.

2/16/2015 1:21 PM Justin
- Bug fixed on class name doesn't assign when create new GRA and cause user can always change header info.

1/22/2016 11:20 AM Qiu Ying
- Show gst_selling_price and selling price

5/2/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

9/4/2018 5:15 PM Justin
- Bug fixed on showing empty selling price when GRA is not under GST status.

5/2/2019 2:44 PM Liew
- Enhanced to display Old Code
*}
{literal}
<style>
.gst_sp{
	color:blue;
	font-size:xx-small;
}
</style>
{/literal}

{if $limits}
<p class=small><img src="ui/flag.png" align=absmiddle> Table below shows <span class=hilite>last 50</span> entries</p>
{/if}
<div class="table-responsive mt-2" style="border: 1px solid gray;border-radius: 5px;">
	<table width=100% class="report_table tabl-sm mb-0 text-md-nowrap mt-2 table-hover"
	>
		<thead class="bg-gray-100">
			<tr>
				<th>No</th>
				<th>&nbsp;</th>
				{if $smarty.request.rm_function}
				<th>P# </th>
				{/if}
				<th>ARMS Code<BR>Old Code</th>
				<th>SKU</th>
				<th>Qty (pcs)</th>
				{if $gra_items[0].sku_type ne 'CONSIGN'}
				<th>Cost</th>
				{/if}
				<th>Selling Price</th>
				<th width=16><img src="/ui/pixel.gif" width=16></th>
			</tr>
		</thead>
		<tbody style="height:200px;overflow:auto;" class="fs-08">
		{section name=i loop=$gra_items}
		<tr height=24 id="tbrow_{$gra_items[i].id}" bgcolor="{cycle values="#eeeeee,"}" {if $smarty.request.rm_function}class="assigned_items"{/if}>
			{if $is_current_item}<input type="hidden" name="current_gra_items[]" value="{$gra_items[i].id}" />{/if}
			<th>{$smarty.section.i.iteration}</th>
			<td width=20>
			{if $smarty.request.add_function}<a href="javascript:void({$smarty.request.add_function}({$gra_items[i].id}))"><img src="ui/table_add.png" align=absmiddle border=0 title="Add item"></a>
			{else}<a href="javascript:void(rm_item({$gra_items[i].id}))" onclick="return confirm('Click OK to remove.')"><img src="ui/table_delete.png" align=absmiddle border=0 title="Remove item"></a>
			&nbsp;
			{/if}
			</td>
			{if $smarty.request.rm_function}
			<td align=center>{$gra_items[i].batchno|ifzero:"-"}</td>
			{/if}
			<td>{$gra_items[i].sku_item_code}<BR>{$gra_items[i].link_code|default:'-'}</td>
			<td>{$gra_items[i].sku} {include file=details.uom.tpl uom=$gra_items[i].packing_uom_code}</td>
			<td align=center>{$gra_items[i].qty}</td>
		
			{if $gra_items[i].sku_type ne 'CONSIGN'}
			<td align=center>{$gra_items[i].cost|number_format:$dp}</td>
			{/if}
			
			
			<td align=center>
				{if $gra_items[i].sku_type eq 'CONSIGN' && $gra_items[i].gst_selling_price}
					{if $gra_items[i].inclusive_tax eq 'yes'}
						{$gra_items[i].gst_selling_price|number_format:2}<br/>
						<span class="gst_sp">(Excl: {$gra_items[i].selling_price|number_format:4})<span>
					{else}
						{$gra_items[i].selling_price|number_format:2}<br/>
						<span class="gst_sp">(Incl: {$gra_items[i].gst_selling_price|number_format:2})<span>
					{/if}
				{else}
					{$gra_items[i].selling_price|number_format:2}
				{/if}
			</td>
		</tr>
		{/section}
		</table>
</div>

<script>
header_disabled();
</script>
