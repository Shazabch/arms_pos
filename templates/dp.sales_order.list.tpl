{*
4/10/2013 5:08 PM Andy
- Remove batch code.
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

    <table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
		<tr bgcolor="#ffee99">
			<th width="60">&nbsp;</th>
			<th width="100">Order No</th>
			<th width="100">Customer PO</th>
			{*<th>To</th>*}
			<th>Amount</th>
			<th>Qty</th>
			
			<th>Assigned Owner</th>
			<th>Order Date</th>
			<th>Last Update</th>
		</tr>
		{foreach from=$order_list item=order}
		    <tr bgcolor="{cycle values=",#eeeeee"}">
		        <td align="center">
					{if $order.status eq '2'}<!-- Rejected -->
					    <a href="dp.sales_order.php?a=open&id={$order.id}&branch_id={$order.branch_id}"><img src="ui/rejected.png" title="Open this Order" border="0" /></a>
					{elseif $order.status eq '4' || $order.status eq '5'}
					    <a href="dp.sales_order.php?a=view&id={$order.id}&branch_id={$order.branch_id}"><img src="ui/cancel.png" title="View this Order" border="0" /></a>
					{elseif $order.status eq '1'}
                        <a href="dp.sales_order.php?a=view&id={$order.id}&branch_id={$order.branch_id}"><img src="ui/view.png" title="View this Order" border="0" /></a>
					{else}
					    <a href="dp.sales_order.php?a=open&id={$order.id}&branch_id={$order.branch_id}"><img src="ui/ed.png" title="Open this Order" border="0" /></a>
					{/if}
					{if $order.active eq 1 and ($order.status eq 1 or $order.status eq 0)}
						{*<a href="javascript:void(show_print_dialog('{$order.id}','{$order.branch_id}'))"><img src="ui/print.png" title="Print Order" border=0></a>*}
					{/if}
				</td>
		        <td nowrap>{$order.order_no}
		        	{*
				 	{if preg_match('/\d/',$order.approvals)}
					<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$order.approvals aorder_id=$order.approval_order_id}</font></div>
					{/if}
					*}
		        </td>
		        <td align="center">{$order.cust_po|default:'-'}</td>
		        {*<td>
					DEBTOR: {$order.debtor_code}<br />
					 <span class="small" style="color:blue;">({$order.debtor_description})</span>
				</td>*}
				<td class="r">{$order.total_amount|number_format:2}</td>
				<td class="r">{$order.total_qty|qty_nf}</td>
				
				<td align="center">{$order.username}</td>
				<td align="center">{$order.order_date}</td>
				<td align="center">{$order.last_update}</td>
		    </tr>
		{/foreach}
	</table>
{/if}
