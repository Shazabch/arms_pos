{*
2/11/2011 11:23:30 AM Andy
- Fix some word.
*}
<b>Stock take date from</b>
<select name="date_from">
	<option value="">-- Please Select --</option>
	{foreach from=$date_list item=d}
	    <option value="{$d}" {if $smarty.request.date_from eq $d}selected {/if}>{$d}</option>
	{/foreach}
</select>

<b>to</b>

<select name="date_to">
	<option value="">-- Please Select --</option>
	{foreach from=$date_list item=d}
	    <option value="{$d}" {if $smarty.request.date_to eq $d}selected {/if}>{$d}</option>
	{/foreach}
</select>
