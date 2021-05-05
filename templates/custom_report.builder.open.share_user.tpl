{if !$user_id}
	{assign var=user_id value="__USER_ID__"}
{/if}
{if !$user_label}
	{assign var=user_label value="__USER_LABEL__"}
{/if}
{if !$username}
	{assign var=username value="__USER_NAME__"}
{/if}

<li class="li_share_user" style="position:relative;" id="li_share_user-{$user_id}" username="{$username}">
	<input type="hidden" name="report_shared_additional_control_user[{$user_id}][id]" value="{$user_id}" />
	
	<span class="span_user_label">{$user_label}</span>
	
	<select name="report_shared_additional_control_user[{$user_id}][control_type]">
		<option value="view" >View</option>
		<option value="edit" {if $user_control_type eq 'edit'}selected {/if}>Edit</option>
	</select>
	
	{if $can_edit}
		<div style="top:20%;right:0;position:absolute;"><img src="ui/icons/cancel.png" class="img_delete_share_user" title="Delete" /></div>
	{/if}
</li>