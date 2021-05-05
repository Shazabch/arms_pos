

{if !$batch_list}
	<p align="center"> &nbsp; * No Data Found *</p>
{else}
	{if $total_page >1}
		<div style="padding:2px;float:left;">
			Page
			<select onChange="ACC_STATUS.page_change('{$acc_type}');" id="sel_page-{$acc_type}">
				{section loop=$total_page name=s}
					<option value="{$smarty.section.s.iteration-1}" {if $smarty.request.page_num eq $smarty.section.s.iteration-1}selected {/if}>{$smarty.section.s.iteration}</option>
				{/section}
			</select>
		</div>
	{/if}
	
	<table class="report_table" width="100%">
		<tr class="header">
			<th width="50">&nbsp;</th>
			{if $BRANCH_CODE eq 'HQ'}
				<th>Branch</th>
			{/if}
			<th>Batch No</th>
			<th>Status</th>
			<th>Tax</th>
			<th>Amount Incl. Tax</th>
			<th>Last Update</th>
		</tr>
		
		{foreach from=$batch_list item=r}
			<tr>
				<td align="center">
					<a href="?a=view_details&acc_type={$acc_type}&branch_id={$r.branch_id}&id={$r.id}" target="_blank">
						<img src="ui/view.png" title="View Details" />
					</a>
				</td>
				{if $BRANCH_CODE eq 'HQ'}
					<td align="center">{$r.bcode}</td>
				{/if}
				<td align="center">{$r.batch_id}</td>
				<td align="center">
					<span class="status_color-{$r.status}">
						{$status_desc[$r.status]}
					</span>
				</td>
				<td align="right">{$r.tax_amount|number_format:2}</td>
				<td align="right">{$r.amount|number_format:2}</td>
				<td align="center">{$r.last_update}</td>
			</tr>
		{/foreach}
	</table>
{/if}