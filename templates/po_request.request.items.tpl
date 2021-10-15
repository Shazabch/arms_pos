{*
Revision History
================
4/24/2007 5:23:16 PM  Gary
- show Artno/Mcode column

7/3/2009 5:00 PM Andy
- add sales trend column if $config.po_request_show_sales_trend=1

8/7/2009 3:43:51 PM Andy
- Add System Stock column

9/2/2009 1:35:40 PM Andy
- column cost and sell added into database, just use it, no need to checking whether use grn , po or master

9/29/2010 11:42:01 AM Andy
- Show request by user.
- Show approve / reject by user.

9/23/2011 10:55:45 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

4/20/2012 5:47:34 PM Alex
- add packing uom code after description

06/24/2020 02:43 PM Sheila
- Fixed table boxes alignment and width.

*}
{if $msg}
<script>
alert('{$msg}');
</script>
{/if}
{$pagination}
{if $limits}
<p class=small><img src="ui/flag.png" align=absmiddle> Table below shows <span class=hilite>last 50</span> entries</p>
{/if}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table  class=" table mb-0 text-md-nowrap  table-hover" width=100%>
				<thead class="bg-gray-100">
					<tr >
						<th>&nbsp;</th>
						<th>ARMS Code</th>
						<th>Artno/MCode</th>
						<th>SKU</th>
						<th>Qty</th>
						<th>uom</th>
						<th>Cost</th>
						<th>Sell</th>
						<th>Total Cost</th>
						<th>Total Selling</th>
						<th>Balance</th>
						<th>Request by</th>
						<th>Added</th>
						<th>Remarks</th>
						<th>Approve / Reject by</th>
						<th>Comment</th>
						{if $config.po_request_show_sales_trend}
							<th nowrap>Sales Trend<br>
								<input class="tbl_col_salestrend" style="color:#000 !important;order:1px solid #ccc;background:#ccc; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="1M" disabled="">
								<input class="tbl_col_salestrend" style="color:#000 !important;border:1px solid #ccc;background:#ddd; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="3M" disabled="">
								<input class="tbl_col_salestrend" style="color:#000 !important;border:1px solid #ccc;background:#ccc; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="6M" disabled="">
								<input class="tbl_col_salestrend" style="color:#000 !important;border:1px solid #ccc;background:#ddd; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="12M" disabled="">	
							</th>
							<th>System<br />Stock</th>
						{/if}
						{*<th width=16><img src="/ui/pixel.gif" width=16></th>*}
					</tr>
				</thead>
				<tbody class="fs-08">
				{assign var=total_qty value=0}
				{assign var=total_cost_amt value=0}
				{assign var=total_sell_amt value=0}
				
				{section name=i loop=$request_items}
				
				{assign var=total_qty value=$total_qty+$request_items[i].qty}
				{assign var=total_cost_amt value=$total_cost_amt+$request_items[i].cost*$request_items[i].fraction*$request_items[i].qty}
				{assign var=total_sell_amt value=$total_sell_amt+$request_items[i].sell*$request_items[i].fraction*$request_items[i].qty}
				<tbody class="fs-08">
					<tr id="tbrow_{$request_items[i].id}" bgcolor="{cycle values="#eeeeee,"}">
						<td width=20 align=center>
						{if $request_items[i].status==0}
						<a href="javascript:void(del_item({$request_items[i].id}))"><img src="ui/remove16.png" align=absmiddle border=0 title="Remove item"></a>
						{else}
						{$smarty.section.i.iteration}
						{/if}
						</td>
						<td>{$request_items[i].sku_item_code}</td>
						{if $request_items[i].artno!=''}
						<td>{$request_items[i].artno}</td>
						{else}
						<td>{$request_items[i].mcode}</td>
						{/if}
						<td>{$request_items[i].sku} {include file=details.uom.tpl uom=$request_items[i].packing_uom_code}</td>
						<td align=center>{$request_items[i].qty|qty_nf}</td>
						<td align=center>{$request_items[i].uom_code|default:'EACH'}</td>
						<td align=center>
						{$request_items[i].cost|number_format:$config.global_cost_decimal_points}
						</td>
						<td align=center>
						{$request_items[i].sell|number_format:2}
						</td>
					
						<td align=center>{$request_items[i].cost*$request_items[i].fraction*$request_items[i].qty|number_format:2}</td>
					
						<td align=center>{$request_items[i].sell*$request_items[i].fraction*$request_items[i].qty|number_format:2}</td>
						<td align=center>{$request_items[i].balance|qty_nf}</td>
						<td>{$request_items[i].u}</td>
						<td>{$request_items[i].added}</td>
						<td>{$request_items[i].comment}</td>
						<td>{$request_items[i].approve_by_user}</td>
						<td>{$request_items[i].reject_comment}</td>
						{if $config.po_request_show_sales_trend}
							<td align=center nowrap>
							<div align=center>
								<input name="sales_trend[{$request_items[i].id}][qty][1]" size=5 style="width:40px;background:#ccc;" value="{$request_items[i].sales_trend.qty.1|qty_nf:".":""|ifzero}" readonly>
								<input name="sales_trend[{$request_items[i].id}][qty][3]" style="width:40px; background:#ddd;" size=5 value="{$request_items[i].sales_trend.qty.3|qty_nf:".":""|ifzero}" readonly>
								<input name="sales_trend[{$request_items[i].id}][qty][6]" size=5 style="width:40px;background:#ccc;" value="{$request_items[i].sales_trend.qty.6|qty_nf:".":""|ifzero}" readonly>
								<input name="sales_trend[{$request_items[i].id}][qty][12]" style="width:40px; background:#ddd;" size=5 value="{$request_items[i].sales_trend.qty.12|qty_nf:".":""|ifzero}" readonly>
							</div>
							<div align=center>
								<input size=5 style="width:40px;background:#ccc;" value="{$request_items[i].sales_trend.qty.1|qty_nf:".":""|ifzero}" readonly>
								<input style="width:40px; background:#ddd;" size=5 value="{$request_items[i].sales_trend.qty.3/3|qty_nf:".":""|ifzero}" readonly>
								<input size=5 style="width:40px;background:#ccc;" value="{$request_items[i].sales_trend.qty.6/6|qty_nf:".":""|ifzero}" readonly>
								<input style="width:40px; background:#ddd;" size=5 value="{$request_items[i].sales_trend.qty.12/12|qty_nf:".":""|ifzero}" readonly>
							</div>
							</td>
							<td>{$request_items[i].system_stock|qty_nf}</td>
						{/if}
						<!--<td align=center>
						{if $request_items[i].status==1}
						<img src="ui/approved.png" align=absmiddle border=0 title="Approved">
						{elseif $request_items[i].status==2}
						<img src="ui/rejected.png" align=absmiddle border=0 title="Rejected"> <br>({$request_items[i].reject_comment})
						{elseif $request_items[i].status==3}
						<img src="ui/cancel.png" align=absmiddle border=0 title="Cancel"> <br>({$request_items[i].reject_comment})
						{elseif $request_items[i].status==0}
						<img src="ui/approved_grey.png" align=absmiddle border=0 title="Not Processed">
						{/if}
						</td>-->
					</tr>
				</tbody>
				{/section}
				</tbody>
				<tr id="tbrow_{$request_items[i].id}" bgcolor="#ffee99">
					<td width="20">&nbsp;   </td>
					<td colspan="3">&nbsp;</td>
					<th align="center">{$total_qty|qty_nf}</td>
					<td colspan="3">&nbsp;</td>
					<th align="center">{$total_cost_amt|number_format:2}</td>
					<th align="center">{$total_sell_amt|number_format:2}</td>
					{assign var=colspan value=6}
					{if $config.po_request_show_sales_trend}{assign var=colspan value=$colspan+2}{/if}
					<td colspan="{$colspan}">&nbsp;</td>
				</tr>
				
				</table>
		</div>
	</div>
</div>
