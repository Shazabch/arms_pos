<div class="row ml-5">
	<div class="col">
		<b class="form-label">Shelf No From</b>
<select class="form-control" name='shelf_no_from'>
	{foreach from=$shelf_no_list item=r}
	    <option value="{$r.shelf_no}" {if $smarty.request.shelf_no_from eq $r.shelf_no}selected {/if}>{$r.shelf_no}</option>
	{foreachelse}
	    <option value="">-- No Data --</option>
	{/foreach}
</select>
	</div>

<div class="col">
	<b class="form-label">To</b>
<select class="form-control" name='shelf_no_to'>
    {foreach from=$shelf_no_list item=r}
	    <option value="{$r.shelf_no}" {if $smarty.request.shelf_no_to eq $r.shelf_no}selected {/if}>{$r.shelf_no}</option>
	{foreachelse}
	    <option value="">-- No Data --</option>
	{/foreach}
</select>	
</div>
<div class="form-label">
	<input type='checkbox' name='all_shelf_no' onChange='toggle_all_shelf_no();' {if $smarty.request.all_shelf_no}checked {/if}> <b>&nbsp;All</b>
<script>toggle_all_shelf_no();</script>
</div>
</div>


