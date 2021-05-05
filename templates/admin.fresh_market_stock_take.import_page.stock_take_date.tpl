<b>Date</b><select name="stock_take_date" onchange="stock_date_changed('{$type}')">
{if $available_date}
	<option value="">-- Please Select --</option>
	{foreach from=$available_date item=val}
		<option value="{$val.date}">{$val.date}</option>
	{/foreach}
{else}
	<option value="">-- No Data --</option>
{/if}
</select>

