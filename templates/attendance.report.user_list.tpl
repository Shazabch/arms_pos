{if $user_list}
	<ul style="list-style:none;">
		<li> <input type="checkbox" id="inp_toggle_all_user" onChange="toggle_all_user();" {if !isset($smarty.request.user_id_list)}checked {/if} /> All</li>
		{foreach from=$user_list key=user_id item=user}
			<li> <input type="checkbox" class="inp_user_id_list" name="user_id_list[{$user_id}]" value="{$user_id}" {if !isset($smarty.request.user_id_list) || $smarty.request.user_id_list.$user_id}checked {/if} /> {$user.u} [{$user.fullname|default:'-'}]</li>
		{/foreach}
	</ul>
{else}
	No User Found
{/if}