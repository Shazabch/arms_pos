{*
7/3/2009 5:00 PM Andy
- add sales trend column if $config.po_request_show_sales_trend=1

8/7/2009 4:23:56 PM Andy
- add system stock column

9/12/2011 2:00:42 PM Alex
- open delete button

9/20/2011 12:28:11 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

4/20/2012 5:47:34 PM Alex
- add packing uom code after description

01/27/2016 13:41 Edwin
- Bug fixed on Cost and Sell price show differently in PO Request Approval after PO Request is created.
*}

{if count($form)>0}
<form method=post name=f_p>
<input type=hidden name=branch value="{$form[i].branch_id}">
<input type=hidden name=reject_comment value="">
<input type=hidden name=a value="create_po">
<input type=hidden name=department_id value={$smarty.request.department_id}>

<div class="card mx-3">
	<div class="card-body">
<p>
<div class="alert alert-primary rounded">
	- Select items to approve or reject.
</div>
</p>

		<div class="table-responsive">
			<table id=tb class="table mb-0 text-md-nowrap  table-hover" width=100% >
			<thead class="bg-gray-100">
				<tr >
					<th align=left ><input type="checkbox" onclick="CheckAll(this);"></th>
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
					<th>Qty</th>
					<th>UOM</th>
					<th>Cost</th>
					<th>Sell</th>
					<th>Total Cost</th>
					<th>Total Sell</th>
					<th>Balance</th>
					<th>Propose Qty</th>
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
				
				{section name=i loop=$form}
				<tbody class="fs-08">
					<tr bgcolor="{cycle values="#eeeeee,"}">
						<td width=40 nowrap align=center>
						<input type="checkbox" name="sel[{$form[i].branch_id}][{$form[i].id}]">
						<a href="javascript:void(do_delete_one({$form[i].branch_id},{$form[i].id}))"><img src=/ui/del.png border=0></a></td>
						<td>{$form[i].sku_item_code}</td>
						{if $form[i].artno!=''}
						<td>{$form[i].artno}</td>
						{else}
						<td>{$form[i].mcode}</td>
						{/if}
						<td>{$form[i].sku} {include file=details.uom.tpl uom=$form[i].packing_uom_code}</td>
						<td>{$form[i].u}</td>
						{if $BRANCH_CODE eq 'HQ'}
						<td>{$form[i].branch}</td>
						{/if}
						<td>
						{$form[i].po_no|default:"-"}
						</td>
						<td>{if $form[i].order_price==0}-{else}{$form[i].order_price|number_format:$config.global_cost_decimal_points}{/if}</td>
						<td>{$form[i].po_added|date_format:'%d/%m/%y'|default:'-'}</td>
						<td align=center>{$form[i].qty|qty_nf}</td>
						<td>{$form[i].uom_code|default:'EACH'}</td>
						<td align=center>{$form[i].cost|number_format:$config.global_cost_decimal_points}</td>
						<td align=center>{$form[i].sell|number_format:2}</td>
						<td align=center>{$form[i].cost*$form[i].fraction*$form[i].qty|number_format:2}</td>
						<td align=center>{$form[i].sell*$form[i].fraction*$form[i].qty|number_format:2}</td>
						<td align=center>{$form[i].balance|qty_nf}</td>
						<td align=center><input name=propose_qty[{$form[i].branch_id}][{$form[i].id}] value="{$form[i].qty}" size=3></td>
						{if $config.po_request_show_sales_trend}
							<td align=center nowrap>
							<div align=center>
								<input name="sales_trend[{$form[i].id}][qty][1]" size=5 style="width:30px;background:#ccc;" value="{$form[i].sales_trend.qty.1|qty_nf:".":""|ifzero}" readonly>
								<input name="sales_trend[{$form[i].id}][qty][3]" style="width:30px; background:#ddd;" size=5 value="{$form[i].sales_trend.qty.3|qty_nf:".":""|ifzero}" readonly>
								<input name="sales_trend[{$form[i].id}][qty][6]" size=5 style="width:30px;background:#ccc;" value="{$form[i].sales_trend.qty.6|qty_nf:".":""|ifzero}" readonly>
								<input name="sales_trend[{$form[i].id}][qty][12]" style="width:30px; background:#ddd;" size=5 value="{$form[i].sales_trend.qty.12|qty_nf:".":""|ifzero}" readonly>
							</div>
							<div align=center>
								<input size=5 style="width:30px;background:#ccc;" value="{$form[i].sales_trend.qty.1|qty_nf:".":""|ifzero}" readonly>
								<input style="width:30px; background:#ddd;" size=5 value="{$form[i].sales_trend.qty.3/3|qty_nf:".":""|ifzero}" readonly>
								<input size=5 style="width:30px;background:#ccc;" value="{$form[i].sales_trend.qty.6/6|qty_nf:".":""|ifzero}" readonly>
								<input style="width:30px; background:#ddd;" size=5 value="{$form[i].sales_trend.qty.12/12|qty_nf:".":""|ifzero}" readonly>
							</div>
							</td>
							<td>{$form[i].system_stock|qty_nf}</td>
						{/if}
					</tr>
				</tbody>
				{/section}
				</table>
		</div>
	</div>
</div>
<input type=hidden name=delete_id value="">
</form>

<p id=submitbtn align=center>
<input name=bsubmit class="btn btn-warning" type=button value="Approve Selected"  onclick="do_approve();">
&nbsp;&nbsp;&nbsp;
<input type=button  class="btn btn-success" value="Reject Selected"  onclick="do_delete()">
</p>
{else}
<p><img src=/ui/bananaman.gif align=absmiddle> There is no PO Request for this department</p>
{/if}
