{*
Revision History
================
4/25/2007  Gary
- show Artno/Mcode column
- reject with reason function

4/25/2007 5:20:29 PM  yinsee
- add show PO history

7/6/2009 9:49 AM Andy
- add sales trend column if $config.po_request_show_sales_trend=1

9/20/2011 10:48:25 AM Alex
- add checking check box when reject items

9/23/2011 10:55:45 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

4/20/2012 5:47:34 PM Alex
- add packing uom code after description

3/27/2015 1:03 PM Andy
- Fix a bug which causing order price always zero.

6/11/2015 4:03 PM Justin
- Bug fixed on gst selling price is missing when generate new PO.

01/27/2016 13:41 Edwin
- Bug fixed on Cost and Sell price show differently after PO request is approved.
*}
<div class="card card-body" id="price_history" style="z-index: 1000000000; display:none;position:absolute;width:350px;height:275px;background:#fff;padding:5px;">

	<div id="price_history_list" style="width:350px;height:290px;overflow:auto;"></div>
	<div align=center style="padding-top:5px">
	<input class="btn btn-danger" type=button onclick="Element.hide(this.parentNode.parentNode)" value="Close">
	</div>
</div>

{if $by_vendor}
<form action="{$smarty.server.PHP_SELF}" method=post name=f_p>
<input type=hidden name=a value="create_po">
<input type=hidden name=form_branch_id value="{$smarty.request.branch_id}">
<input type=hidden name=reject_comment value="">
<input type=hidden name=branch_id value="">
<input type=hidden name=delete_id value="">
<input type=hidden name=department_id value={$smarty.request.department_id}>
<div class="card mx-3" style="z-index: 1;">
	<div class="card-body">
		
			<p>
			<div class="alert alert-primary rounded">
				- Select items under the same vendor and click "Create PO" button to create PO for that vendor.
			</div>
				</p>
			<div class="table-responsive">
				<table id=tb class="table mb-0 text-md-nowrap  table-hover" width=100% >
				<thead class="bg-gray-100">
					<tr height=24 >
						<th>&nbsp;</th>
						<th width="80">&nbsp;</th>
						<th>ARMS Code</th>
						<th>Artno/Mcode</th>
						<th>SKU</th>
						<th>Request by</th>
						{if $BRANCH_CODE eq 'HQ'}
						<th>Branch</th>
						{/if}
						<th>Last PO</th>
						<th>Last<br>PO Price</th>
						<th>Last<br>PO Date</th>
						<th>Qty </th>
						<th>Proposed Qty </th>
						<th>UOM</th>
						<th>Cost</th>
						<th>Sell</th>
						<th>Total Cost</th>
						<th>Total Sell</th>
						<th>Balance</th>
						{if $config.po_request_show_sales_trend}
							<th nowrap>Sales Trend<br>
								<span style="border:1px solid #ccc;background:#ccc">&nbsp;&nbsp;1M&nbsp;&nbsp;</span>
								<span style="border:1px solid #ccc;background:#ddd">&nbsp;&nbsp;3M&nbsp;&nbsp;</span>
								<span style="border:1px solid #ccc;background:#ccc">&nbsp;&nbsp;6M&nbsp;&nbsp;</span>
								<span style="border:1px solid #ccc;background:#ddd">&nbsp;&nbsp;12M&nbsp;&nbsp;</span>
							</th>
							<th>System<br />Stock</th>
						{/if}
					</tr>
				</thead>
				{foreach from=$by_vendor item=vendor}
				<tbody class="fs-08" id=tbody_{$vendor.id}>
				<tr bgcolor=#ffffee>
					<td><input type=radio name=vendor_id value="{$vendor.id}" onclick="check_all('tbody_{$vendor.id}', this)"></td>
					<td align=center colspan=2 nowrap>
						<input class="btn btn-primary" type=button value="Create PO" onclick="if (confirm('Press OK to create PO from selected vendor and items.')) form.submit();">
						&nbsp;&nbsp;&nbsp;&nbsp;
						<input class="btn btn-danger" type=button value="Reject Selected" onclick="multiple_reject_items({$vendor.branch_id});">
					</td>
					<td colspan='20'><h3>{$vendor.name}</h3></td>
				</tr>
				{foreach from=$vendor.items item=request_item}
				<tr bgcolor="{cycle values="#eeeeee,"}">
					<td>
						<input type=checkbox name=sel[{$vendor.id}][{$request_item.id}] class="select_box">
					</td>
					<td>
						<img src=/ui/del.png title="reject" onclick="reject_items({$request_item.id},{$request_item.branch_id})">
						<img src=/ui/table_multiple.png title="History" onclick="get_price_history(this,{$request_item.sku_item_id})"></a>
					</td>
					
					<input type=hidden name=sku_item_id[{$vendor.id}][{$request_item.id}] value={$request_item.sku_item_id}>
					<input type=hidden name=artno[{$vendor.id}][{$request_item.id}] value={$request_item.artno}>
					<input type=hidden name=ext_branch_id[{$vendor.id}][{$request_item.id}] value={$request_item.branch_id}>
					<input type=hidden name=mcode[{$vendor.id}][{$request_item.id}] value={$request_item.mcode}>
					<input type=hidden name=order_uom_id[{$vendor.id}][{$request_item.id}] value={$request_item.uom_id}>
					<input type=hidden name=order_uom_fraction[{$vendor.id}][{$request_item.id}] value={$request_item.fraction}>
					<input type=hidden name=qty[{$vendor.id}][{$request_item.id}] value={$request_item.qty}>
					<input type=hidden name=selling_price[{$vendor.id}][{$request_item.id}] value={$request_item.selling_price}>
					<input type=hidden name=gst_selling_price[{$vendor.id}][{$request_item.id}] value={$request_item.gst_selling_price}>
					<input type=hidden name=order_price[{$vendor.id}][{$request_item.id}] value={$request_item.cost_price}>
					<input type=hidden name=balance[{$vendor.id}][{$request_item.id}] value={$request_item.balance}>
					<td>{$request_item.sku_item_code}</td>
					{if $request_item.artno!=''}
					<td>{$request_item.artno}</td>
					{else}
					<td>{$request_item.mcode}</td>
					{/if}
					<td>{$request_item.sku} {include file=details.uom.tpl uom=$request_item.packing_uom_code}</td>
					<td>{$request_item.u}</td>
					{if $BRANCH_CODE eq 'HQ'}
					<td>{$request_item.branch}</td>
					{/if}
					<td>
					{$request_item.po_no|default:"-"}
					</td>
					<td>{if $request_item.order_price==0}-{else}{$request_item.order_price|number_format:$config.global_cost_decimal_points}{/if}</td>
					<td>{$request_item.po_added|date_format:'%d/%m/%y'|default:'-'}</td>
					<td align=center>{$request_item.qty|qty_nf}</td>
					<td align=center>{$request_item.propose_qty|qty_nf}</td>
					<td align=center>{$request_item.uom_code|default:'EACH'}</td>
					<td align=center>{$request_item.cost_price|number_format:$config.global_cost_decimal_points}</td>
					<td align=center>{$request_item.selling_price|number_format:2}</td>
					<td align=center>{$request_item.qty*$request_item.fraction*$request_item.cost_price|number_format:2}</td>
					<td align=center>{$request_item.qty*$request_item.fraction*$request_item.selling_price|number_format:2}</td>
					<td align=center>{$request_item.balance|qty_nf}</td>
					{if $config.po_request_show_sales_trend}
						<td align=center nowrap>
						<div align=center>
							<input name="sales_trend[{$vendor.id}][{$request_item.id}][qty][1]" size=5 style="width:30px;background:#ccc;" value="{$request_item.sales_trend.qty.1|qty_nf:".":""|ifzero}" readonly>
							<input name="sales_trend[{$vendor.id}][{$request_item.id}][qty][3]" style="width:30px; background:#ddd;" size=5 value="{$request_item.sales_trend.qty.3|qty_nf:".":""|ifzero}" readonly>
							<input name="sales_trend[{$vendor.id}][{$request_item.id}][qty][6]" size=5 style="width:30px;background:#ccc;" value="{$request_item.sales_trend.qty.6|qty_nf:".":""|ifzero}" readonly>
							<input name="sales_trend[{$vendor.id}][{$request_item.id}][qty][12]" style="width:30px; background:#ddd;" size=5 value="{$request_item.sales_trend.qty.12|qty_nf:".":""|ifzero}" readonly>
						</div>
						<div align=center>
							<input size=5 style="width:30px;background:#ccc;" value="{$request_item.sales_trend.qty.1|qty_nf:".":""|ifzero}" readonly>
							<input style="width:30px; background:#ddd;" size=5 value="{$request_item.sales_trend.qty.3/3|qty_nf:".":""|ifzero}" readonly>
							<input size=5 style="width:30px;background:#ccc;" value="{$request_item.sales_trend.qty.6/6|qty_nf:".":""|ifzero}" readonly>
							<input style="width:30px; background:#ddd;" size=5 value="{$request_item.sales_trend.qty.12/12|qty_nf:".":""|ifzero}" readonly>
						</div>
						</td>
						<td>{$request_item.system_stock|qty_nf}</td>
					{/if}
				</tr>
				{/foreach}
				</tbody>
				{/foreach}
				</table>
		</div>
	</div>
</div>
</form>

<script>check_all()</script>
{else}
<div class="alert alert-primary rounded mx-3">
	<p><img src=/ui/bananaman.gif align=absmiddle> There is no PO Request for this department</p>
</div>
{/if}
