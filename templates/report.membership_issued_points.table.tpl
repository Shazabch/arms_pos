{*
1/25/2017 5:31 PM Andy
- Fixed group by monthly not working.
*}

<table class="report_table" width="100%">
	<tr class="header">
		<th>Date</th>
		{foreach from=$selected_issue_type_list item=t}
			<th width="200">{$issued_type_list.$t}</th>
		{/foreach}
		<th width="200">Total</th>
	</tr>
	
	{foreach from=$report_data.by_date key=d item=point_info}
		<tr>
			<td>{if $group_type eq 'month'}{$data.date_label.$d}{else}{$d}{/if}</td>
			{foreach from=$selected_issue_type_list item=t}
				<td align="right">{$point_info.$t.points|number_format}</td>
			{/foreach}
			<td align="right">{$point_info.total.points|number_format}</td>
		</tr>
	{/foreach}
	
	<tr class="header">
		<th align="right">Total</th>
		{foreach from=$selected_issue_type_list item=t}
			<th align="right">{$report_data.total.$t.points|number_format}</th>
		{/foreach}
		<th align="right">{$report_data.total.total.points|number_format}</th>
	</tr>
</table>