{*
9/3/2012 3:23 PM Andy
- Fix pagination problem.

9/13/2012 5:29 PM Andy
- Add new purchase agreement type, Seasonal.
*}

{if !$pa_list}
	<p>No Data</p>
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
	
	<table width="100%" cellspacing="1" cellpadding="4" border="0" style="padding:2px">
		<tr bgcolor="#ffee99">
			<th>&nbsp;</th>
			<th>No.</th>
			<th>Vendor Code</th>
			{if $config.enable_vendor_account_id}
				<th>Account ID</th>
			{/if}
			<th>Vendor</th>
			<th>Department</th>
			<th>Type</th>
			<th>Date From</th>
			<th>Date To</th>
			<th>Last Update</th>
		</tr>
		{foreach from=$pa_list item=pa}
			<tr bgcolor="{cycle values="#ffffff,#eeeeee"}">
				<td>			
					{if !$pa.status}
					    {if $pa.branch_id!=$sessioninfo.branch_id}
							<a href="?a=view&id={$pa.id}&branch_id={$pa.branch_id}"><img src="ui/approved.png" title="View" border="0" /></a>
						{else}
							<a href="?a=open&id={$pa.id}&branch_id={$pa.branch_id}"><img src="ui/ed.png" title="Edit" border="0" /></a>
						{/if}
			            {* <a href="javascript:void(print_promotion('{$promo_list[i].branch_id}','{$promo_list[i].id}'))"><img src="ui/print.png" border="0" title="Print Promotion" /></a>*}
			        {elseif $pa.status==2}
						<a href="?a=open&id={$pa.id}&branch_id={$pa.branch_id}" target="_blank"><img src="ui/rejected.png" title="View" border="0" /></a>
					{elseif $pa.status==4 or $pa.status==5}
						<a href="?a=view&id={$pa.id}&branch_id={$pa.branch_id}" target="_blank"><img src="ui/cancel.png" title="View" border="0" /></a>
					{else}
						<a href="?a=view&id={$pa.id}&branch_id={$pa.branch_id}" target="_blank"><img src="ui/approved.png" title="View" border="0" /></a>
						{*<a href="javascript:void(print_promotion('{$promo_list[i].branch_id}','{$promo_list[i].id}'))"><img src="ui/print.png" border="0" title="Print Promotion" /></a>*}
					{/if}
				</td>
				<td>{$pa.report_prefix}{$pa.id|string_format:"%05d"}</td>
				<td>{$pa.vcode|default:'-'}</td>
				{if $config.enable_vendor_account_id}
					<td>{$pa.account_id|default:'-'}</td>
				{/if}
				<td>{$pa.vdesc|default:'-'}
					{if preg_match('/\d/',$pa.approvals) and $pa.status==1}
						<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$pa.approvals aorder_id=$pa.approval_order_id}</font></div>
					{/if}
				</td>
				<td>{$pa.dept_name|default:'-'}</td>
				<td align="center">{$pa.pa_type}</td>
				<td>{$pa.date_from|default:'-'}</td>
				<td>{$pa.date_to|default:'-'}</td>
				<td>{$pa.last_update}</td>
			</tr>
		{/foreach}
	</table>
{/if}