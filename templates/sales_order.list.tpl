{*
8/8/2011 11:05:11 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

11/23/2011 2:55:34 PM Justin
- Modified the printing feature to redirect into report printing menu.

4/5/2012 1:43:04 PM Andy
- Add show relationship between PO and SO.

3/4/2013 11:51 AM Andy
- Add get receipt list when load the sales order which has been exported to POS.

4/12/2013 4:33 PM Andy
- Enhance to show a warning icon and ask user to open and save again the Sales Order if found SO Amount need update.

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

1/5/2016 4:00 PM Qiu Ying
- Editing only allowed according to login branch (for saved order)

3/3/2021 17:54 PM Sin Rou
- Added config check to hide "Batch Code" in column table.
*}

{if !$order_list}
	<p align="center"> &nbsp; * No Order Found *</p>
{else}
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

	{if $smarty.request.t eq 6 or $smarty.request.t eq 7}
		{assign var=show_delivered_qty value=1}
	{/if}
	{if $smarty.request.t eq 4 or $smarty.request.t eq 6 or $smarty.request.t eq 7}
		{assign var=show_po value=1}
	{/if}
  <div class="table-responsive">
	<table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
	<thead class="bg-gray-100">
		<tr>
			<th width="60">&nbsp;</th>
			{if !$config.sales_order_hide_batch_code}
				<th width="100">Batch Code</th>
			{/if}
			<th width="100">Order No</th>
			<th width="100">Customer PO</th>
			<th>To</th>
			<th>Amount</th>
			<th>Qty</th>
			{if $smarty.request.t eq 8}<th>Receipt No</th>{/if}
			{if $show_delivered_qty}
			    <th>Delivered Qty</th>
			{/if}
			
			{if $show_po}
				<th>PO</th>
			{/if}
			
			<th>Created By</th>
			<th>Order Date</th>
			<th>Last Update</th>
		</tr>
	</thead>
		{foreach from=$order_list item=order}
		   <tbody class="fs-08">
			<tr bgcolor="{cycle values=",#eeeeee"}">
		        <td align="center">
					{if $order.status eq '2'}<!-- Rejected -->
					    <a href="sales_order.php?a={if $order.user_id eq $sessioninfo.id}open{else}view{/if}&id={$order.id}&branch_id={$order.branch_id}"><img src="ui/rejected.png" title="Open this Order" border="0" /></a>
					{elseif $order.status eq '4' || $order.status eq '5'}
					    <a href="sales_order.php?a=view&id={$order.id}&branch_id={$order.branch_id}"><img src="ui/cancel.png" title="Open this Order" border="0" /></a>
					{elseif $order.status eq '1'}
                        <a href="sales_order.php?a=view&id={$order.id}&branch_id={$order.branch_id}"><img src="ui/view.png" title="View this Order" border="0" /></a>
					{else}
						{if $sessioninfo.branch_id == $order.branch_id}
							<a href="sales_order.php?a=open&id={$order.id}&branch_id={$order.branch_id}"><img src="ui/ed.png" title="Open this Order" border="0" /></a>
						{else}
							<a href="sales_order.php?a=view&id={$order.id}&branch_id={$order.branch_id}"><img src="ui/view.png" title="View this Order" border="0" /></a>
						{/if}
					{/if}
					{if $order.active eq 1 and ($order.status eq 1 or $order.status eq 0)}
					<a href="javascript:void(show_print_dialog('{$order.id}','{$order.branch_id}'))"><img src="ui/print.png" title="Print Order" border=0></a>
					{/if}
				</td>
				
				{if !$config.sales_order_hide_batch_code}
					<td nowrap>{$order.batch_code}</td>
				{/if}

		        <td nowrap>{$order.order_no}
				 	{if preg_match('/\d/',$order.approvals)}
					<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$order.approvals aorder_id=$order.approval_order_id}</font></div>
					{/if}
		        </td>
		        <td align="center">{$order.cust_po|default:'-'}</td>
		        <td>
					DEBTOR: {$order.debtor_code}<br />
					 <span class="small" style="color:blue;">({$order.debtor_description})</span>
				</td>
				<td class="r">
					{if $order.amt_need_update}
						<img src="/ui/messages.gif" align="absmiddle" title="Please open and save again to correct the amount." />
					{/if}
					{$order.total_amount|number_format:2}				
				</td>
				<td class="r">{$order.total_qty|qty_nf}</td>
				{if $smarty.request.t eq 8}
				<td align="center">
					{foreach from=$order.receipt_details item=receipt_detail}
						{assign var=rd value=$receipt_detail}
						<a href="javascript:void(0);" onclick="trans_detail('{$rd.counter_id}','{$rd.cashier_id}','{$rd.date}','{$rd.pos_id}','{$rd.branch_id}');">{receipt_no_prefix_format branch_id=$rd.branch_id counter_id=$rd.counter_id receipt_no=$rd.receipt_no}</a>&nbsp;&nbsp;
					{/foreach}
				</td>
				{/if}
				{if $show_delivered_qty}
				    <td class="r" style="{if $order.delivered_qty>=$order.total_qty}color:green;{/if}">{$order.delivered_qty|qty_nf}</td>
				{/if}
				{if $show_po}
					<td align="center">
						{foreach from=$order.po_list item=po_info name=pof}
							{if !$smarty.foreach.pof.first}, {/if}
							<a href="po.php?a=view&branch_id={$po_info.bid}&id={$po_info.po_id}" target="_blank">
								{$po_info.code}
							</a>
						{/foreach}
					</td>
				{/if}
				<td align="center">{$order.username}</td>
				<td align="center">{$order.order_date}</td>
				<td align="center">{$order.last_update}</td>
		    </tr>

		   </tbody>		{/foreach}
	</table>
  </div>
{/if}
