<select name="batch_code">
	{if $batch_code_list}
		<option value="all">-- All --</option>
		{foreach from=$batch_code_list item=bc}
			<option value="{$bc}" {if $smarty.request.batch_code eq $bc}selected {/if}>{$bc}</option>
		{/foreach}
	{else}
		<option value="NO_DATA">-- No Data --</option>
	{/if}
</select>