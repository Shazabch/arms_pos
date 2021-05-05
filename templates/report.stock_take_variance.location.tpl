<b>Location From</b>
<select name='location_from' onchange="load_shelf_no();">
	{foreach from=$location_list item=r}
	    <option value="{$r.location}" {if $smarty.request.location_from eq $r.location}selected {/if}>{$r.location}</option>
	{foreachelse}
	    <option value="">-- No Data --</option>
	{/foreach}
</select>&nbsp;

<b>To</b>&nbsp;
<select name='location_to' onchange="load_shelf_no();">
    {foreach from=$location_list item=r}
	    <option value="{$r.location}" {if $smarty.request.location_to eq $r.location}selected {/if}>{$r.location}</option>
	{foreachelse}
	    <option value="">-- No Data --</option>
	{/foreach}
</select>

<input type='checkbox' name='all_location' onChange='toggle_all_location(); load_shelf_no();' {if $smarty.request.all_location}checked {/if}> <b>All</b>
<script>toggle_all_location();</script>
