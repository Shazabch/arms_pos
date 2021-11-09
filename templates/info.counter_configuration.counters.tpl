{*
*}
<b class="form-label">Counter</b>
<select class="form-control" name="counter_id">
	<option value="">-- All --</option>
	{if $counter_list}
		{foreach from=$counter_list key=cid item=r}
			<option value="{$cid}">{$r.network_name}</option>
		{/foreach}
	{/if}
</select>
&nbsp;&nbsp;&nbsp;