
<form name="f_leave_record" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_save_user_leave_record" />
	<input type="hidden" name="is_new" value="{$is_new}" />
	<input type="hidden" name="guid" value="{$form.guid}" />
	<input type="hidden" name="user_id" value="{$form.user_id}" />
	
	
	<table width="100%">
		{* User *}
		<tr>
			<td><b>User</b></td>
			<td colspan="2">
				{$user.u}
			</td>
		</tr>
		
		{* Branch *}
		<tr>
			<td><b>Branch</b></td>
			<td>
				{if $BRANCH_CODE eq 'HQ'}	
					<select name="branch_id" class="required" title="Branch">
						<option value="">-- Please Select --</option>
						{foreach from=$branch_list key=bid item=b}
							<option value="{$bid}" {if $form.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/foreach}
					</select>&nbsp;&nbsp;&nbsp;&nbsp;					
				{else}
					<input type="hidden" name="branch_id" value="{$form.branch_id}" />
					{$branch_list[$form.branch_id].code}
				{/if}
			</td>
		</tr>
		
		{* Leave *}
		<tr>
			<td><b>Leave</b></td>
			<td>
				<select name="leave_id" class="required" title="Leave">
					<option value="">-- Please Select --</option>
					{foreach from=$leave_list key=leave_id item=r}
						<option value="{$leave_id}" {if $form.leave_id eq $leave_id}selected {/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		
		{* Date From *}
		<tr>
			<td><b>Date From</b></td>
			<td colspan="2">
				<input type="text" name="date_from" value="{$form.date_from}" id="inp_date_from" readonly="1" size="12" class="required" title="Date From" />
				<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
			</td>
		</tr>
		
		{* Date To *}
		<tr>
			<td><b>Date To</b></td>
			<td colspan="2">
				<input type="text" name="date_to" value="{$form.date_to}" id="inp_date_to" readonly="1" size="12" class="required" title="Date To" />
				<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;
			</td>
		</tr>
	</table>
		
</form>

<p id="p_action" align="center">
	<input type="button" value="Save" id="btn_save" onClick="USER_LEAVE_DIALOG.save_clicked();" />
	<input type="button" value="Close" onClick="USER_LEAVE_DIALOG.close();" />
</p>