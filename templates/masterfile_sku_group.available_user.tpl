<ul style="list-style-type:none; margin-left:0;padding:0;" class="small">
{foreach from=$available_users item=r name=f}
	{assign var=uid value=$r.id}
	<li {if $smarty.foreach.f.iteration%2 eq 0}style="background:#efefef;"{/if}><input type="checkbox" name="allowed_user[{$uid}]" value="{$uid}" align="absmiddle" {if $selected_users.$uid}checked {/if} class="allowed_user" /> {$r.u} ({$r.bcode})</li>
{/foreach}
</ul>
