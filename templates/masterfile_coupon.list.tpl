{*
9/11/2013 5:00 PM Justin
- Enhanced to rework some of the components such as add/edit Coupon to use pop up dialog.

8/29/2019 11:23 AM Andy
- Added coupon printed item row.
- Enhanced to able to view coupon details while it is already activated.
*}

{$pagination}
<table id="coupon_list_id" border=0 cellspacing=1 cellpadding=4 width=100%>
	<tr bgcolor=#ffee99>
		{if $privilege.MST_COUPON_EDIT}<th rowspan=2>&nbsp;</th>{/if}
		<th rowspan=2>Code</th>
		<th rowspan=2>Department</th>
		<th rowspan=2>Brand</th>
		<th rowspan=2>Vendor</th>
		<th rowspan=2>SKU Items</th>
		<th rowspan=2>Print</th>
		<th colspan=2>Valid Date</th>
		<th colspan=2>Valid Time</th>
		<th rowspan=2>Activated Date</th>
		<th rowspan=2>Last Updated</th>
		<th rowspan=2>Added</th>
		<th rowspan=2>Created By</th>
	</tr>
	<tr bgcolor=#ffee99>
		<th>Start</th>
		<th>End</th>
		<th>Start</th>
		<th>End</th>
	</tr>
	<tbody id="coupon_details">
	{if $details}
		{foreach from=$details item=cou}
			<tr id="row_i{$cou.id}_b{$cou.branch_id}">
				{if $privilege.MST_COUPON_EDIT}
					<td id="act_img_i{$cou.id}_b{$cou.branch_id}" width="5%" align="center" nowrap>
					<img width='15px' src="{if !$cou.active}ui/ed.png{else}ui/view.png{/if}" align="absmiddle" border="0" title='{if !$cou.active}Edit{else}View{/if}' onclick="COUPON_DIALOG.toggle_coupon_dialog('{$cou.id}','{$cou.branch_id}');">&nbsp;&nbsp;
					{if $cou.active}
						<img width='15px' src="ui/deact.png" align="absmiddle" border="0" title='Deactivate' onclick="activate_deactivate_coupon('{$cou.id}','{$cou.branch_id}','deactivate','{$cou.dept_id|default:0}','{$cou.brand_id}','{$cou.vendor_id}');">&nbsp;&nbsp;
					{else}
						<img width='15px' src="ui/act.png" align="absmiddle" border="0" title='Activate' onclick="activate_deactivate_coupon('{$cou.id}','{$cou.branch_id}','activate','{$cou.dept_id|default:0}','{$cou.brand_id}','{$cou.vendor_id}');">&nbsp;&nbsp;
						{*
						<img width='15px' src="/ui/rejected.png" onclick="if (confirm('Are you sure?')) delete_coupon('{$cou.id}','{$cou.branch_id}');" title="Delete" align=absmiddle border=0>&nbsp;&nbsp;
						*}
					{/if}
					<img width='15px' src="/ui/print.png" onclick="COUPON_DIALOG.toggle_print_coupon_dialog('{$cou.id}','{$cou.branch_id}');" title="Print" align=absmiddle border=0>&nbsp;&nbsp;

					</td>
				{/if}
				<td>{$cou.code}</td>
				<td>
					{if !$cou.si_list}
						{$cou.dept_desc}
					{else}
						&nbsp;
					{/if}
				</td>
				<td>{$cou.brand_desc}</td>
				<td>{$cou.vendor_desc}</td>
				<td>
					{if $cou.si_list}
						{foreach from=$cou.si_list item=si}
							{$si.sku_item_code} - {$si.description}<br />
						{/foreach}
					{else}
						&nbsp;
					{/if}
				</td>
				<td align="right">{$cou.is_print}</td>
				<td align="center">{$cou.valid_from|ifzero}</td>
				<td align="center">{$cou.valid_to|ifzero}</td>
				<td align="center">{$cou.time_from|substr:0:5}</td>
				<td align="center">{$cou.time_to|substr:0:5}</td>
				<td align="center">{$cou.activated|ifzero}</td>
				<td align="center">{$cou.last_update|ifzero}</td>
				<td align="center">{$cou.added}</td>
				<td align="center">{$cou.user_name}</td>
			</tr>
			{if $cou.coupon_items_list}
				{foreach from=$cou.coupon_items_list item=ci}
					<tr>
						{if $privilege.MST_COUPON_EDIT}<td>&nbsp;</td>{/if}
						<td>
							{if $cou.member_limit_type and $privilege.MST_COUPON_EDIT}
								<a href="?a=manage_member&coupon_code={$ci.coupon_code}" target="_blank">
									<img src="ui/icons/star.png" align="absmiddle" class="clickable" title="View Members Usage" border="0" />
								</a>
							{/if}
							&nbsp;
						</td>
						<td colspan="3">{$ci.coupon_code}</td>
						<td>
							{if $cou.discount_by eq 'per'}
								Percent: {$ci.print_value}%
							{else}
								Amount: {$ci.print_value|number_format:2}
							{/if}
						</td>
						<td align="right">
							{$ci.print_qty|number_format}
						</td>
						
						<td colspan="6">&nbsp;</td>
						<td align="center">{$ci.added}</td>
						<td align="center">{$ci.printed_by}</td>
					</tr>
				{/foreach}
			{/if}
		{/foreach}
	{else}
	<tr>
		<td colspan=14 align="center">-- No Data --</td>
	</tr>
	{/if}
	</tbody>
</table>
