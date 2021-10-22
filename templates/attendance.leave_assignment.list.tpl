<div class="stdframe">
		
	
	<div class="card mx-3">
		<div class="card-body">
	<input type="hidden" id="tmp_bid" value="{$smarty.request.branch_id}" />
	<input type="button" class="btn btn-primary" value="Add New Record" onClick="USER_LEAVE_DIALOG.open('{$user_id}','');" /><br /><br />

			<div class="table-responsive">
				<table width="100%" class="report_table">
				<thead class="bg-gray-100">
					<tr class="header">
						<th width="50">&nbsp;</th>
						<th width="50">&nbsp;</th>
						<th>Leave</th>
						{if $BRANCH_CODE eq 'HQ'}
							<th width="150">Branch</th>
						{/if}
						<th width="150">Date From</th>
						<th width="150">Date To</th>
						<th width="150">Added</th>
					</tr>
				</thead>
					
					{if !$record_list}
					<tbody class="fs-08">
						<tr>
							{assign var=cols value=6}
							{if $BRANCH_CODE eq 'HQ'}{assign var=cols value=$cols+1}{/if}
							<td colspan="{$cols}">* No Data *</td>
						</tr>
					</tbody>
					{else}
						{foreach from=$record_list item=r name=fl}
							<tbody class="fs-08">
								<tr>
									<td nowrap>
										<img src="/ui/ed.png" class="clickable" title="Edit" onClick="USER_LEAVE_DIALOG.open('{$r.user_id}', '{$r.guid}');" />
										<img src="/ui/del.png" class="clickable" title="Delete" onClick="ATTENDANCE_LEAVE_ASSIGN.del_record_clicked('{$r.guid}');" />
									</td>
									
									{* Number *}
									<td align="center">{$smarty.foreach.fl.iteration}</td>
									
									{* Leave *}
									<td>
										{$r.leave_code} - {$r.leave_desc}
									</td>
									
									{if $BRANCH_CODE eq 'HQ'}
										{* Branch *}
										<td align="center">
											{$branch_list[$r.branch_id].code}
										</td>
									{/if}
									
									{* Date From *}
									<td align="center">
										{$r.date_from}
									</td>
									
									{* Date To *}
									<td align="center">
										{$r.date_to}
									</td>
									
									{* Added *}
									<td align="center">
										{$r.added}
									</td>
								</tr>
							</tbody>
						{/foreach}
					{/if}
				</table>
			</div>
		</div>
	</div>
</div>