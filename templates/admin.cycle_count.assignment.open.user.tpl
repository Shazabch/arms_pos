{assign var=user_id value=$user.id}

<div class="div_username_container" id="div_user_assign-{$user_type}-{$user_id}">
	<input type="hidden" name="{$user_type}_user_list[{$user_id}]" value="{$user_id}" />
	{$user.u}
	{if $can_edit}
		<img src="ui/closewin.png" align="absmiddle" style="float:right;" class="clickable" title="Delete User" onClick="CC_ASSGN.del_user_assign('{$user_type}', '{$user_id}')" />
	{/if}
</div>