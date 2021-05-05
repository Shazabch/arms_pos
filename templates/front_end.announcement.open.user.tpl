{assign var=user_id value=$user.id}

<div class="div_username_container" id="div_user_assign-{$user_id}">
	<input type="hidden" name="announcement_user_id[{$user_id}]" value="{$user_id}" />
	{$user.u}
	{if !$readonly}
		<img src="ui/closewin.png" align="absmiddle" style="float:right;" class="clickable" title="Delete User" onClick="BRANCH_ASSGN.del_user_assign('{$user_id}')" />
	{/if}
</div>