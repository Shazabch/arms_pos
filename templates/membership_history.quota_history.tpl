
{if !$quota_history_list}
	<p align="center">- No History -</p>
{else}
	<table width="100%" cellspacing="1" cellpadding="4" border="0">
		<thead>
			<tr bgcolor="#ffee99">
				<th>{$config.membership_cardname} No</th>
				<th>Branch</th>
				<th>Remark</th>
				<th>Date</th>
				<th>User</th>	
				<th>Type</th>
				<th width="5%">Quota</th>
				<th width=16>&nbsp;</th>
			</tr>
		</thead>
		
		<tbody>
			{foreach from=$quota_history_list item=qh}
				<tr bgcolor={cycle values=",#eeeeee"}>
					<td>{$qh.card_no|default:'&nbsp'}</td>
					<td>{$qh.code|default:"&nbsp;"}</td>
					<td>{$qh.remark|default:"&nbsp;"}</td>
					<td align="center">
						{if $qh.type eq 'POS'}
							<a href="javascript:void(show_quota_info('{$qh.quota_date|date_format:"%Y-%m-%d"}', '{$qh.card_no}', '{$qh.branch_id}'))">
								{$qh.quota_date|default:"&nbsp;"}
							</a>
						{else}
							{$qh.quota_date|default:"&nbsp;"}
						{/if}
					</td>
					<td align="center">{$qh.user|default:"&nbsp;"}</td>
					<td align="center">{$qh.type|default:"&nbsp;"}</td>
					<td align="right">{$qh.quota_amount|default:"&nbsp;"}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/if}
