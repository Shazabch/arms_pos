{if $available_users}
	<ul style="list-style:none;padding-left:0;" class="ul_list">
		{foreach from=$available_users item=r}
		    <li ><input type="checkbox" name="allowed_user[]" value="{$r.id}" {if is_array($allowed_user) and in_array($r.id, $allowed_user)}checked {/if} id="inp_allowed_user_{$r.id}" />
				<label for="inp_allowed_user_{$r.id}">{$r.u}</label>
			</li>
		{/foreach}
	</ul>
{else}
	No user found.
{/if}
