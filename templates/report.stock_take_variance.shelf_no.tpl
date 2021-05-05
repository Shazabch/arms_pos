<b>Shelf No From</b>&nbsp;
<select name='shelf_no_from'>
	{foreach from=$shelf_no_list item=r}
	    <option value="{$r.shelf_no}" {if $smarty.request.shelf_no_from eq $r.shelf_no}selected {/if}>{$r.shelf_no}</option>
	{foreachelse}
	    <option value="">-- No Data --</option>
	{/foreach}
</select>&nbsp;

<b>To</b>&nbsp;
<select name='shelf_no_to'>
    {foreach from=$shelf_no_list item=r}
	    <option value="{$r.shelf_no}" {if $smarty.request.shelf_no_to eq $r.shelf_no}selected {/if}>{$r.shelf_no}</option>
	{foreachelse}
	    <option value="">-- No Data --</option>
	{/foreach}
</select>

<input type='checkbox' name='all_shelf_no' onChange='toggle_all_shelf_no();' {if $smarty.request.all_shelf_no}checked {/if}> <b>All</b>
<script>toggle_all_shelf_no();</script>
