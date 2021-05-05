{*
1/20/2011 2:09:25 PM Justin
- Added created and approved by columns.

1/14/2013 2:09 PM Justin
- Enhanced to show voucher value for user to maintain.
*}

<tr class="tr_item {if $smarty.request.highlight_item_id eq $item.sku_item_id || $item.days_left <= $config.membership_redemption_expire_days && $item.days_left > 0 && $item.active eq '1'}highlight_row{/if}" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" id="tr_item,{$item.branch_id}_{$item.id}">
	{if $smarty.request.t ne '3'}
		<td align="center">
				<input type="checkbox" class="chx_item" title="Select this item" name="sel_item[{$item.branch_id}_{$item.id}]" value="1" align="absmiddle" id="inp_item,{$item.branch_id},{$item.id}" />
		</td>
	{/if}
	<td align="center" class="td_no" width="20">{$smarty.foreach.fitem.iteration}.</td>
    <td align="center">{$item.bcode}</td>
    <td align="center" width="200">
		<div id="div_ab_list,{$item.branch_id}_{$item.id}" class="crop" >
		{if $item.available_branches}
		    {assign var=ab_count value=0}
			{foreach from=$item.available_branches key=bid item=dummy name=fab}
			    {assign var=ab_count value=$ab_count+1}
			    {if $ab_count<=5}{$branches.$bid.code}{if !$smarty.foreach.fab.last},&nbsp;{/if}{/if}
			{/foreach}
		{/if}
		</div>
		<span id="span_ab_total_b,{$item.branch_id}_{$item.id}" style="font-weight:bold;">{if $ab_count>1}({$ab_count} branches){/if}</span>
	</td> 
	<td>{$item.sku_item_code|default:"-"}</td>
	<td>{$item.description}</td>
	<td align="right">{$item.grn_cost|number_format:$config.global_cost_decimal_points}</td>
	<td align="right">{$item.selling_price|number_format:2|ifzero:"-"}</td>
	<td align="right">{$item.qty|qty_nf}</td>
	<td align="right">{$item.point}</td>
	<td align="right">{$item.cash|default:0}</td>
	<td align="center">{$item.valid_date_from|ifzero:'-'}</td>
	<td align="center">{$item.valid_date_to|ifzero:'-'}</td>
	<td align="right">{$item.receipt_amount}</td>
	<td align="center">{$item.receipt_date_from|ifzero:'-'}</td>
	<td align="center">{$item.receipt_date_to|ifzero:'-'}</td>
	<td align="center">{if $item.use_curr_date}<img src="ui/checked.gif" style="vertical-align:top;">{else}<img src="ui/unchecked.gif" style="vertical-align:top;">{/if}</td>
	{if $smarty.request.t eq '3'}
		{if $item.cancel_by}
			<td class="nl">{$item.cancel_user}</td>
			<td class="nl">{$item.cancel_date}</td>
		{else}
			<td colspan="2" class="nl">Expired - System Updated on {$item.cancel_date}</td>
		{/if}
	{/if}
	<td align="center">{$item.create_user}</td>
	{if $smarty.request.t ne '1'}
		<td align="center">{$item.approve_user}</td>
	{/if}
	{if $config.membership_use_voucher}
		<td align="right">{$item.voucher_value|number_format:2}</td>
	{/if}
</tr>
