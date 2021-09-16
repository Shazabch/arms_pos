<tr id="tr-approval-{$user_id}" class="tr-approval">
	<td nowrap>
		{* check all *}
		<i class="fas fa-check-square text-primary" title="Check all" onClick="APPROVAL_FLOW.update_user_approval_notify_settings('approval','{$user_id}', true);" ></i>
		{* un-check all *}
		<i class="far fa-square text-primary" title="Uncheck all" onClick="APPROVAL_FLOW.update_user_approval_notify_settings('approval','{$user_id}', false);"></i>
		<input type="hidden" name="approval_user_id[{$user_id}]" value="{$user_id}" />
	</td>
	<td nowrap>
		{* delete row *}
		<img src="ui/icons/cross.png" class="clickable" title="Delete" onClick="APPROVAL_FLOW.delete_approval('{$user_id}');" />
		{* Move Up *}
		<img src="ui/icons/arrow_up.png" class="clickable img-approval-up" title="Move Up" id="img-approval-up-{$user_id}" onClick="APPROVAL_FLOW.move_approval('{$user_id}', 'up');" />
		{* Move Down *}
		<img src="ui/icons/arrow_down.png" class="clickable img-approval-down" title="Move Down" id="img-approval-down-{$user_id}" onClick="APPROVAL_FLOW.move_approval('{$user_id}', 'down');" />
	</td>
	<td>{$users_info.$user_id.u}</td>
	<td align="center">{$users_info.$user_id.default_branch_code}</td>
	<td align="center"><input type="checkbox" name="approval_settings[approval][{$user_id}][pm]" class="chx-approval chx-approval-pm" value="1" title="PM" {if $form.approval_settings.approval.$user_id.pm}checked {/if} /></td>
	<td align="center"><input type="checkbox" name="approval_settings[approval][{$user_id}][email]" class="chx-approval chx-approval-email" value="1" title="Email" {if $form.approval_settings.approval.$user_id.email}checked {/if}/></td>
	<td align="center"><input type="checkbox" name="approval_settings[approval][{$user_id}][sms]" class="chx-approval chx-approval-sms" value="1" title="SMS" {if $form.approval_settings.approval.$user_id.sms}checked {/if} /></td>
	<td align="center">
		<input type="input" size="10" name="approval_settings[approval][{$user_id}][min_doc_amt]" class="chx-approval-min_doc_amt form-control" value="{$form.approval_settings.approval.$user_id.min_doc_amt|default:0|number_format:2:".":""|ifzero:''}" title="Minimum Document Amount" style="text-align:right;" onChange="APPROVAL_FLOW.min_doc_amt_changed('approval', '{$user_id}');" />
	</td>
</tr>