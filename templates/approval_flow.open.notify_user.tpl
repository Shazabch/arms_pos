<tr id="tr-notify-{$user_id}" class="tr-notify">
	<td nowrap>
		{* check all *}
		<i class="fas fa-check-square text-primary" title="Check all" onClick="APPROVAL_FLOW.update_user_approval_notify_settings('notify','{$user_id}', true);"></i>

		{* un-check all *}
		<i class="far fa-square text-primary" title="Uncheck all" onClick="APPROVAL_FLOW.update_user_approval_notify_settings('notify','{$user_id}', false);"></i>
		<input type="hidden" name="notify_user_id[{$user_id}]" value="{$user_id}" />
	</td>
	<td nowrap>
		{* delete row *}
		<img src="ui/icons/cross.png" class="clickable" title="Delete" onClick="APPROVAL_FLOW.delete_notify('{$user_id}');" />
	</td>
	<td>{$users_info.$user_id.u}</td>
	<td align="center">{$users_info.$user_id.default_branch_code}</td>
	<td align="center"><input type="checkbox" name="approval_settings[notify][{$user_id}][pm]" class="chx-notify chx-notify-pm" value="1" title="PM" {if $form.approval_settings.notify.$user_id.pm}checked {/if} /></td>
	<td align="center"><input type="checkbox" name="approval_settings[notify][{$user_id}][email]" class="chx-notify chx-notify-email" value="1" title="Email" {if $form.approval_settings.notify.$user_id.email}checked {/if}/></td>
	<td align="center"><input type="checkbox" name="approval_settings[notify][{$user_id}][sms]" class="chx-notify chx-notify-sms" value="1" title="SMS" {if $form.approval_settings.notify.$user_id.sms}checked {/if} /></td>
</tr>