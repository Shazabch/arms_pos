{*
 11/3/2009 5:39:54 PM Andy
 - Add Rejected information and edit column
 
 11/23/2009 5:05:25 PM Andy
 - Add stock balance column for requested branch
 - Add Sales Trend column
 
9/29/2010 10:56:02 AM Andy
- Show request by user.
- Show reject by user.

4/12/2011 10:18:57 AM Andy
- Show Request Date.

10/13/2011 11:35:46 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

11/21/2011 2:59:29 PM Andy
- Add show Ctn#1 and Ctn#2 at DO Request if found config.do_request_show_ctn_1_2

4/20/2012 5:47:34 PM Alex
- add packing uom code after description

12/10/2012 2:09 PM Andy
- Add Expected Delivery Date. can be disabled by config "do_request_no_expected_delivery_date".
- Change sales trend AVG 1M to AVG per Days.

2/19/2013 4:32 PM Justin
- Enhanced to show department column.

3/5/2013 4:20 PM Andy
- Enhance to show Group Stock Balance By.

3/7/2013 3:22 PM Andy
- Enhance to show Group Stock Balance From.

03/24/2016 09:30 Edwin
- Modified on table label name

7/6/2017 12:07 PM Andy
- Enhanced to able to highlight DO Request Item by SKU_ITEM_ID.

3/21/2019 3:10 PM Andy
- Enhanced to show DO Request SKU Photo.

5/28/2019 2:46 PM Justin
- Enhanced to have remove items by selection. 

06/23/2020 05:13 Sheila
- Updated button css.

12/11/2020 05:05 Rayleen
- Move Current Request Qty after Photo column
- Add Old Code link after Mcode
- Add additional information data when searching SKU

12/23/2020 02:58 Rayleen
- Add Color and Size in the Description Column
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

{if count($items)>0 and $smarty.request.t eq 1}
	<div style="padding:5px;float:left;">
		<button class="btn btn-error" title="Remove Selected Items" onClick="remove_item('','');"><img src="/ui/pm_remove.png" border="0" align="absmiddle" /> Remove Selected Items</button>
	</div>
{/if}
<div class="card mx-3">
	<div class="card-body">
		
<div class="row mb-2">
	<div class="col-md-2"><b class="form-label mt-2 text-center">Sort By</b></div>
<div class="col-md-4">
	<select class="form-control" id="sort_by">
		{foreach from=$sort_list key=r item=desc}
			<option value="{$r}" {if $sort_by eq $r}selected{/if}>{$desc}</option>
		{/foreach}
	</select>
</div>
<div class="col-md-4">
	<select class="form-control" id="sort_order">
		<option value="asc" {if $sort_order eq "asc"}selected{/if}>Ascending</option>
		<option value="desc" {if $sort_order eq "desc"}selected{/if}>Descending</option>
	</select>
</div>
<div class="col-md-2">
	<button class="btn btn-info" onclick="list_sel('{$curr_tab}');">Refresh</button>
</div>
</div>

{if $highlight_sku_item_id}
	<br />
	<span style="color:blue;">* Display selected item only.</span>
{/if}
<div class="table-responsive">
	<table id="items_tbl" width="100%" class="report_table table mb-0 text-md-nowrap  table-hover">
		<thead class="bg-gray-100">
			<tr >
				<th rowspan="2">
					{if count($items)>0 and $smarty.request.t eq 1}
						<input type="checkbox" onChange="toggle_all_item_selected(this);" title="Toggle Selection of all items in this page" />
					{/if}
				</th>
				{if $config.do_request_show_sku_photo}
					<th rowspan="2">Photos</th>
				{/if}
				<th rowspan="2">Current<br />Request<br />Qty</th>
				<th rowspan="2">ARMS Code</th>
				<th rowspan="2">Art No.</th>
				<th rowspan="2">MCode</th>
				<th rowspan="2">{$config.link_code_name}</th>
				<th rowspan="2">Description</th>
				<th rowspan="2">Department</th>
				<th rowspan="2">Request by</th>
				<th rowspan="2">Location</th>
				<th rowspan="2">Supply<br />Branch</th>
				<th rowspan="2">Request Date</th>
				
				{* Expected Delivery Date *}
				{if !$config.do_request_no_expected_delivery_date}
					<th rowspan="2">Expected<br />Delivery Date</th>
				{/if}
				
				{if $smarty.request.t eq 2}
					<th rowspan="2">Process<br />By</th>
				{/if}
				{*<th>UOM</th>*}
				<th colspan="4">Stock Balance</th>
				{if $smarty.request.t eq 3}
					<th rowspan="2">Total<br />Request<br />Qty</th>
				{/if}
				<th rowspan="2">Delivered<br />Qty</th>
				{if $smarty.request.t eq 3}
					<th rowspan="2">PO<br />Qty</th>
				{/if}
				{if $config.do_request_show_ctn_1_2}
					<th rowspan="2">CTN#1</th>
					<th rowspan="2">CTN#2</th>
				{/if}
				
				{if $smarty.request.t eq 2 or $smarty.request.t eq 4}
					<th rowspan="2">DO<br />Qty</th>
				{/if}
				<th rowspan="2">Selling<br />Price</th>
				<th rowspan="2">Remarks</th>
				{if $smarty.request.t eq 5 or $smarty.request.t eq 4 or $smarty.request.t eq 3}
					<th colspan="2">Reject</th>
				{/if}
				<th colspan="4">Sales Trend</th>
				<th rowspan="2">Last Update</th>
				<th rowspan="2">Generated<br />DO/PO</th>
			</tr>
			<tr>
				<th>Request Branch</th>
				<th>Request Branch (Group)</th>
				<th>Supply Branch</th>
				<th>Supply Branch (Group)</th>
				{if $smarty.request.t eq 5 or $smarty.request.t eq 4 or $smarty.request.t eq 3}
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
					<input type="hidden" id="inp_packing_uom_fraction-{$r.branch_id}-{$r.id}" value="{$r.packing_uom_fraction}" />
					<div style="float:right;">
						{if $open_mode eq 'open' and $r.status eq 0 and !$r.print_picking_list_by and !$r.po_id}
							<img src="ui/pm_remove.png" border="0" align="absmiddle" style="float:right;" title="Remove Item" class="clickable" onClick="remove_item('{$r.id}','{$r.branch_id}');" />
							<input type="checkbox" border="0" align="absmiddle" style="float:right;" class="inp_item" name="item_id[]" value="{$r.id}" onChange="toggle_item_selected(this);" />
						{/if}
						{if ($smarty.request.t eq 4 or $smarty.request.t eq 5) and $r.status eq 3}
							<img src="/ui/icons/arrow_rotate_anticlockwise.png" border="0" class="clickable" align="absmiddle" title="Revert" onClick="revert_item('{$r.id}','{$r.branch_id}', '{$r.request_qty}');" />
							<img src="/ui/icons/cross.png" border="0" class="clickable" align="absmiddle" title="Cancel" onClick="cancel_item('{$r.id}','{$r.branch_id}');" />
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
				<td rowspan="2" class="r {if $open_mode eq 'open' and $r.status eq 0 and !$r.print_picking_list_by and !$r.po_id}editable clickable{/if}" id="request_qty,{$r.id},{$r.branch_id}">{$r.request_qty}</td>
				<td rowspan="2">{$r.sku_item_code}</td>
				<td rowspan="2">{$r.artno}</td>
				<td rowspan="2">{$r.mcode}</td>
				<td rowspan="2">{$r.link_code}</td>
				<td rowspan="2">
					{$r.description}
					{include file=details.uom.tpl uom=$r.packing_uom_code}
					{if $r.color || $r.size }
						<br>
						{if $r.color }<b>Color:</b> {$r.color}{/if}{if $r.size }{if $r.color }, {/if}<b>Size:</b> {$r.size}{/if}
					{/if}
				</td>
				<td rowspan="2">{$r.department}</td>
				<td rowspan="2">{$r.request_by}</td>
				<td rowspan="2">{$r.location|default:'-'}</td>
				<td rowspan="2">{$r.request_branch_code}</td>
				<td rowspan="2" align="center">{$r.added|date_format:'%Y-%m-%d'}</td>
				
				{* Expected Delivery Date *}
				{if !$config.do_request_no_expected_delivery_date}
					<td rowspan="2" align="center">{if $r.expect_do_date and $r.expect_do_date ne '0000-00-00'}{$r.expect_do_date|date_format:'%Y-%m-%d'}{else}-{/if}</td>
				{/if}
				
				{if $smarty.request.t eq 2}
					<td rowspan="2">{$r.u}</td>
				{/if}
				{*<td align="center">{$r.uom_code}</td>*}
				<td class="r" rowspan="2">{$r.stock_balance|qty_nf}</td>
				<td class="r" rowspan="2">{$r.group_stock_balance|qty_nf}</td>
				<td class="r" rowspan="2">{$r.stock_balance2|qty_nf}</td>
				<td class="r" rowspan="2">{$r.group_stock_balance2|qty_nf}</td>
				
				{if $smarty.request.t eq 3}
					<td class="r" rowspan="2">{$r.default_request_qty|qty_nf}</td>
				{/if}
				<td class="r" rowspan="2">{$r.total_do_qty|qty_nf}</td>
				{if $smarty.request.t eq 3}
					<td class="r" rowspan="2">{$r.po_qty|qty_nf}</td>
				{/if}
				
				{if $config.do_request_show_ctn_1_2}
					<td rowspan="2" align="right">
						<input type="hidden" id="inp_ctn_1_fraction-{$r.branch_id}-{$r.id}" value="{$r.ctn_1_fraction}" />
						{if $r.ctn_1_fraction > 1}
							<span class="small">{$r.ctn_1_code}</span>
							<br />
							<span id="span_ctn_1_qty-{$r.branch_id}-{$r.id}">
								({$r.request_qty*$r.packing_uom_fraction/$r.ctn_1_fraction|qty_nf})
							</span>
						{else}-
						{/if}
					</td>
					<td rowspan="2" align="right">
						<input type="hidden" id="inp_ctn_2_fraction-{$r.branch_id}-{$r.id}" value="{$r.ctn_2_fraction}" />
						{if $r.ctn_2_fraction > 1}
							<span class="small">{$r.ctn_2_code}</span>
							<br />
							<span id="span_ctn_2_qty-{$r.branch_id}-{$r.id}">
								({$r.request_qty*$r.packing_uom_fraction/$r.ctn_2_fraction|qty_nf})
							</span>
						{else}-
						{/if}
					</td>
				{/if}
				{if $smarty.request.t eq 2 or $smarty.request.t eq 4}
					<td class="r" rowspan="2">{$r.do_qty}</td>
				{/if}
				<td class="r" rowspan="2">{$r.selling_price|number_format:2:".":""}</td>
				<td rowspan="2">{$r.comment}</td>
				{if $smarty.request.t eq 5 or $smarty.request.t eq 4 or $smarty.request.t eq 3}
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
				{assign var=cols value=26}
				{if $smarty.request.t eq 2}{assign var=cols value=$cols+2}{/if}
				{if $smarty.request.t eq 3}{assign var=cols value=$cols+2}{/if}
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
	ele.title = "Click to edit";
    Event.observe(ele, 'click', function(event) {
	  	do_edit(this);
	});
});
{/literal}
</script>
