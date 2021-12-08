
	<div class="row ">
		<div class="col">
			<b class="form-label">Location From</b>
	<select class="form-control" name='location_from' onchange="load_shelf_no();">
		{foreach from=$location_list item=r}
			<option value="{$r.location}" {if $smarty.request.location_from eq $r.location}selected {/if}>{$r.location}</option>
		{foreachelse}
			<option value="">-- No Data --</option>
		{/foreach}
	</select>&nbsp;
		</div>
	
	<div class="col">
		<b class="form-label">To</b>	
	<select class="form-control" name='location_to' onchange="load_shelf_no();">
		{foreach from=$location_list item=r}
			<option value="{$r.location}" {if $smarty.request.location_to eq $r.location}selected {/if}>{$r.location}</option>
		{foreachelse}
			<option value="">-- No Data --</option>
		{/foreach}
	</select>
	
	
	</div>
	<div class="form-label mt-4">
		<input type='checkbox' name='all_location' onChange='toggle_all_location(); load_shelf_no();' {if $smarty.request.all_location}checked {/if}> <b>&nbsp;All</b>
	<script>toggle_all_location();</script>
	</div>
	</div>

