{*
1/16/2020 2:51 PM Andy
- Enhanced to show IP for each scan record.
- Enhanced to show old data history.
*}

<div class="stdframe">
	{if $sessioninfo.privilege.ATTENDANCE_USER_MODIFY_ADD}
		<input type="button" value="Add New Record" onClick="DAILY_RECORD_DIALOG.open('{$bid}', '{$user_id}', '');" /><br /><br />
	{/if}
	
	<table width="100%" class="report_table" style="background-color: #fff;">
		<tr class="header">
			<th width="50">&nbsp;</th>
			<th width="150">Date</th>
			<th>Shift</th>
			<th>Scan (IP)</th>
		</tr>
		
		{if !$record_list}
			<tr>
				<td colspan="4">* No Data *</td>
			</tr>
		{else}
			{foreach from=$record_list item=daily_record}
				<tr>
					<td nowrap>
						<img src="/ui/ed.png" class="clickable" title="Edit" onClick="DAILY_RECORD_DIALOG.open('{$daily_record.branch_id}', '{$daily_record.user_id}', '{$daily_record.date}');" />
						<img src="/ui/del.png" class="clickable" title="Delete" onClick="ATTENDANCE_USER_RECORD.del_record_clicked('{$daily_record.branch_id}', '{$daily_record.user_id}', '{$daily_record.date}');" />
						{if $daily_record.modify_count > 0}
							<a href="?a=view_history&branch_id={$daily_record.branch_id}&user_id={$daily_record.user_id}&date={$daily_record.date}" target="_blank">
								<img src="/ui/icons/script.png" title="This record has been modified {$daily_record.modify_count} times, click to view history" />
							</a>
						{/if}
					</td>
					
					{* Date *}
					<td align="center">{$daily_record.date}</td>
					
					{* Shift *}
					<td>
						{if $daily_record.shift_id}
							{$daily_record.shift_code} - {$daily_record.shift_description}
						{else}
							-
						{/if}
					</td>
					
					{* Scan *}
					<td>
						<ul>
							{foreach from=$daily_record.scan_record_list item=scan_record}
								<li> {$scan_record.scan_time}
									{if $scan_record.ip}
										<i class="small">({$scan_record.ip})</i>
									{/if}
								</li>
							{/foreach}
						</ul>
					</td>
				</tr>
			{/foreach}
		{/if}
	</table>
</div>