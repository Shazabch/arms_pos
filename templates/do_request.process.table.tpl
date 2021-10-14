{*
 11/3/2009 5:41:00 PM Andy
 - Add Reject information and column edit
 
 11/23/2009 5:06:03 PM Andy
 - Add stock balance column for requested branch
 - Add Sales Trend column
 
9/29/2010 11:00:39 AM Andy
- Show request by user.
- Show reject by user.

4/12/2011 10:18:57 AM Andy
- Show Request Date.

10/13/2011 11:04:12 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

4/23/2012 2:06:29 PM Alex
- add packing uom code after description

11/26/2012 10:13:00 AM Fithri
- New Tab "Exported to PO" for item (deliver qty < default request qty && po_qty >0)
- can tick and print picking list"

12/10/2012 2:09 PM Andy
- Add Expected Delivery Date. can be disabled by config "do_request_no_expected_delivery_date".
- Add can sort by expected delivery date.
- Change sales trend AVG 1M to AVG per Days.

12/12/2012 5:10 PM Andy
- Remove clicking on expected delivery date sorting.

2/8/2013 2:12 PM Justin
- Enhanced to show user a list of reject reason while found config.

2/19/2013 4:32 PM Justin
- Enhanced to show department column.

3/5/2013 4:20 PM Andy
- Enhance to show Group Stock Balance By.

3/7/2013 3:22 PM Andy
- Enhance to show Group Stock Balance From.

03/24/2016 09:30 Edwin
- Added stock balance status into table when stock balance is <=0
- Modified on table label name

04/12/2016 15:45 Edwin
- Revert back to enable select/unselect all item function

3/21/2019 5:36 PM Andy
- Enhanced to show DO Request SKU Photo.

5/27/2019 5:25 PM Justin
- Enhanced to have reject items by selection.
*}

<script>
{literal}
item_id_list = [];
{/literal}
</script>

{if $total_page >1}
<div style="padding:2px;float:left;">
Page
<select onChange="page_change(this);">
	{section loop=$total_page name=s}
		<option value="{$smarty.section.s.iteration-1}" {if $smarty.request.p eq $smarty.section.s.iteration-1}selected {/if}>{$smarty.section.s.iteration}</option>
	{/section}
</select>
</div>
{/if}

	{if $smarty.request.t eq 1 or $smarty.request.t eq 6}
		<div style="padding:2px;float:left;">
			{if $total_item_count>0}
				<button class="btn_toggle_all_sel" title="Select All {$total_item_count} items" onClick="toggle_all_sel(true);">Select All {$total_item_count} item{if $total_item_count>1}s{/if}</button>
				<button class="btn_toggle_all_sel" title="Unselect All {$total_item_count} items" onClick="toggle_all_sel(false);">Unselect All {$total_item_count} item{if $total_item_count>1}s{/if}</button>
				{if $smarty.request.t neq 6}
					<button title="Generate PO" onClick="show_generate_po_popup();"><img src="/ui/icons/basket_go.png" border="0" align="absmiddle" /> Generate PO</button>
					<button title="Print Picking List" onClick="print_picking_list(false,false);"><img src="/ui/print.png" border="0" align="absmiddle" /> Print Picking List</button>
					<button title="Reject Selected Items" onClick="{if !$config.do_request_reject_reason}reject_item('');{else}show_reject_reason_dialog('');{/if}"><img src="/ui/icons/delete.png" border="0" align="absmiddle" /> Reject Selected Items</button>
				{else}
					<button title="Print Picking List" onClick="print_picking_list(false,true);"><img src="/ui/print.png" border="0" align="absmiddle" /> Print Picking List</button>
				{/if}
			{/if}
		</div>
		{*<div class="r" style="padding:2px;">
		{if $total_item_count>0}
		<a href="javascript:void(show_generate_po_popup())"><img src="/ui/icons/basket_go.png" border="0" align="absmiddle" /> Generate PO</a>&nbsp;
		<a href="javascript:void(print_picking_list())"><img src="/ui/print.png" border="0" align="absmiddle" /> Print Picking List</a>
		{/if}
		</div>
		*}
	{elseif $smarty.request.t eq 2}
		{*<div class="r" style="padding:2px;">
		{if $total_item_count>0}
			<a href="javascript:void(generate_do())"><img src="/ui/icons/page_add.png" border="0" align="absmiddle" /> Generate DO</a>&nbsp;
			<a href="javascript:void(print_picking_list('re_print'))"><img src="/ui/print.png" border="0" align="absmiddle" /> Re-print Picking List</a>
		{/if}
		</div>
		*}
	{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table id="items_tbl" width="100%" class="report_table table mb-0 text-md-nowrap  table-hover">
			<thead class="bg-gray-100">
				<tr >
					<th rowspan="2">
						{if $total_item_count>0 and ($smarty.request.t eq 1 or $smarty.request.t eq 4 or $smarty.request.t eq 6)}
							<input type="checkbox" onChange="toggle_all_item_selected(this);" title="Toggle Selection of all items in this page" />
						{/if}
					</th>
					{if $config.do_request_show_sku_photo}
						<th rowspan="2">Photos</th>
					{/if}
					<th rowspan="2">ARMS Code</th>
					<th rowspan="2">Art No.</th>
					<th rowspan="2">MCode</th>
					<th rowspan="2">Description</th>
					<th rowspan="2">Department</th>
					<th rowspan="2">Request by</th>
					<th rowspan="2">Location</th>
					<th rowspan="2">Request<br />Branch</th>
					<th rowspan="2">Request Date</th>
						
					{* Expected Delivery Date *}
					{if !$config.do_request_no_expected_delivery_date}
						<th rowspan="2">
							Expected<br />Delivery Date
						</th>
					{/if}
					
					{if $smarty.request.t eq 2}
						<th rowspan="2">Process<br />By</th>
					{/if}
					<th colspan="4">Stock Balance</th>
					{if $smarty.request.t eq 3 or $smarty.request.t eq 6}
						<th rowspan="2">Total<br />Request<br />Qty</th>
					{/if}
					<th rowspan="2">Delivered<br />Qty</th>
					{if $smarty.request.t eq 3 or $smarty.request.t eq 6}
						<th rowspan="2">PO<br />Qty</th>
					{/if}
					<th rowspan="2">Current<br />Request<br />Qty</th>
					{if $smarty.request.t eq 2 or $smarty.request.t eq 4}
						<th rowspan="2">DO<br />Qty</th>
					{/if}
					<th rowspan="2">Selling<br />Price</th>
					<th rowspan="2">Remarks</th>
					{if $smarty.request.t eq 5 or $smarty.request.t eq 4 or $smarty.request.t eq 3 or $smarty.request.t eq 6}
						<th colspan="2">Reject</th>
					{/if}
					<th colspan="4">Sales Trend</th>
					<th rowspan="2">Last Update</th>
					<th rowspan="2">Generated<br />DO/PO</th>
				</tr>
				<tr >
					<th>Request Branch</th>
					<th>Request Branch (Group)</th>
					 <th>Supply Branch</th>
					 <th>Supply Branch (Group)</th>
					 
					 {if $smarty.request.t eq 5 or $smarty.request.t eq 4 or $smarty.request.t eq 3 or $smarty.request.t eq 6}
						 <th>By</th>
						 <th>Reason</th>
					 {/if}
					<th>1M</th>
					<th>3M</th>
					<th>6M</th>
					<th>12M</th>
				</tr>
			</thead>
				<tbody id="tbody_item_list" class="fs-08">
				{foreach from=$items item=r}
					<script>
						item_id_list.push('{$r.id}');
					</script>
					{cycle values=",#eeeeee" assign=tr_color}
					<tr bgcolor="{$tr_color}">
						<td width="60" nowrap class="tr_item" rowspan="2">{$item_counter++}.
						<div style="float:right;">
							{if $smarty.request.t eq 1 and $r.status eq 0}
								<img src="/ui/icons/delete.png" border="0" align="absmiddle" style="float:right;" class="clickable" onClick="{if !$config.do_request_reject_reason}reject_item('{$r.id}');{else}show_reject_reason_dialog('{$r.id}');{/if}" title="Reject this item" />
							{/if}
							{if ($smarty.request.t eq 1 or $smarty.request.t eq 4 or $smarty.request.t eq 6) and ($r.status eq 0 or $r.status eq 2)}
								<input type="checkbox" class="inp_item" name="item_id[]" value="{$r.id}" {if $r.selected}checked {/if} onChange="toggle_item_selected(this);" style="float:right;" />
							{/if}
							{if $r.status eq 4}<span style="color:red;cursor:default;" title="Cancelled">[ C ]</span>{/if}
							</div>
						</td>
						{if $config.do_request_show_sku_photo}
							<td rowspan="2" align="center">
								<div>
									{show_sku_photo sku_item_id=$r.sku_item_id container_id="sku_photo_`$r.branch_id`_`$r.id`" show_as_first_image=1}
								</div>
							</td>
						{/if}
						<td rowspan="2">{$r.sku_item_code}{if $r.stock_balance2<=0}<div style="font-size: smaller; color: red">(Out of stock)</div>{/if}</td>
						<td rowspan="2">{$r.artno}</td>
						<td rowspan="2">{$r.mcode}</td>
						<td rowspan="2">{$r.description} {include file=details.uom.tpl uom=$r.packing_uom_code}</td>
						<td rowspan="2">{$r.department}</td>
						<td rowspan="2">{$r.request_by}</td>
						<td rowspan="2">{$r.location|default:'-'}</td>
						<td rowspan="2">{$r.branch_code}</td>
						<td rowspan="2" align="center">{$r.added|date_format:'%Y-%m-%d'}</td>
						
						{* Expected Delivery Date *}
						{if !$config.do_request_no_expected_delivery_date}
							<td rowspan="2" align="center">{if $r.expect_do_date and $r.expect_do_date ne '0000-00-00'}{$r.expect_do_date|date_format:'%Y-%m-%d'}{else}-{/if}</td>
						{/if}
						
						{if $smarty.request.t eq 2}
							<td rowspan="2">{$r.u}</td>
						{/if}
						<td class="r" rowspan="2">{$r.stock_balance|qty_nf}</td>
						<td class="r" rowspan="2">{$r.group_stock_balance|qty_nf}</td>
						<td class="r" rowspan="2">{$r.stock_balance2|qty_nf}</td>
						<td class="r" rowspan="2">{$r.group_stock_balance2|qty_nf}</td>
						
						{if $smarty.request.t eq 3 or $smarty.request.t eq 6}
							<td class="r" rowspan="2">{$r.default_request_qty|qty_nf}</td>
						{/if}
						<td class="r" rowspan="2">{$r.total_do_qty|qty_nf}</td>
						{if $smarty.request.t eq 3 or $smarty.request.t eq 6}
							<td class="r" rowspan="2">{$r.po_qty|qty_nf}</td>
						{/if}
						<td rowspan="2" class="r" id="request_qty,{$r.id},{$r.branch_id}">{$r.request_qty}</td>
						{if $smarty.request.t eq 2 or $smarty.request.t eq 4}
							<td rowspan="2" class="r" id="do_qty,{$r.id},{$r.branch_id}">{$r.do_qty}</td>
						{/if}
						<td class="r" rowspan="2">{$r.selling_price|number_format:2:".":""}</td>
						<td rowspan="2">{$r.comment}</td>
						{if $smarty.request.t eq 5 or $smarty.request.t eq 4 or $smarty.request.t eq 3 or $smarty.request.t eq 6}
							<td rowspan="2">{$r.reject_by_user|default:'-'}</td>
							<td rowspan="2">{$r.reason|default:'-'}</td>
						{/if}
						<td align="right">{$r.sales_trend.qty.1|qty_nf:".":""|ifzero}</td>
						<td align="right">{$r.sales_trend.qty.3|qty_nf:".":""|ifzero}</td>
						<td align="right">{$r.sales_trend.qty.6|qty_nf:".":""|ifzero}</td>
						<td align="right">{$r.sales_trend.qty.12|qty_nf:".":""|ifzero}</td>
						<td align="center" rowspan="2">{$r.last_update}</td>
						<td align="center" rowspan="2">
							{foreach from=$r.do_list item=do_id}
								{if $do_id>0}
									<a href="do.php?a=open&id={$do_id}&branch_id={$r.request_branch_id}&highlight_item_id={$r.sku_item_id}" target="_blank">DO#{$do_id}</a><br />
								{/if}
							{/foreach}
							{if $r.po_id}
								<a href="po.php?a=open&id={$r.po_id}&branch_id={$r.request_branch_id}&highlight_item_id={$r.sku_item_id}" target="_blank">PO#{$r.po_id}</a><br />
							{/if}
						</td>
					</tr>
					<tr bgcolor="{$tr_color}">
						<td align="right">{$r.sales_trend.qty.1/30|qty_nf:".":""|ifzero}</td>
						<td align="right">{$r.sales_trend.qty.3/3|qty_nf:".":""|ifzero}</td>
						<td align="right">{$r.sales_trend.qty.6/6|qty_nf:".":""|ifzero}</td>
						<td align="right">{$r.sales_trend.qty.12/12|qty_nf:".":""|ifzero}</td>
					</tr>
				{foreachelse}
					<tr>
						{assign var=cols value=24}
						{if $smarty.request.t eq 2}{assign var=cols value=$cols+2}{/if}
						{if $smarty.request.t eq 3 or $smarty.request.t eq 6}{assign var=cols value=$cols+3}{/if}
						{if $smarty.request.t eq 4}{assign var=cols value=$cols+2}{/if}
						{if $smarty.request.t eq 5}{assign var=cols value=$cols+1}{/if}
						<td colspan="{$cols}" align="center">No Item</td>
					</tr>
				{/foreach}
				</tbody>
				</table>
				
		</div>
	</div>
</div>
<script type="text/javascript">
{literal}
$$('#items_list td.editable').each(function(ele){
    Event.observe(ele, 'click', function(event) {
	  	do_edit(this);
	});
});
{/literal}
</script>
