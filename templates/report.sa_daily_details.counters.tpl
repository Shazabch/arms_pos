{*
*}
<select class="form-control" name="counter_id">
	<option value="all" {if !$smarty.request.counter_id || $smarty.request.counter_id eq "all"}selected{/if}>-- All --</option>
	{foreach from=$counters item=r}
		<option value="{$r.id}" {if $smarty.request.counter_id eq $r.id}selected {/if}>
			{$r.network_name}
		</option>
	{/foreach}
</select>
