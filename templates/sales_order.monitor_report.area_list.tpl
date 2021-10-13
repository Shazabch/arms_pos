<select class="form-control" name="area" onChange="area_changed();">
	{if $area_list}
		<option value="all">-- All --</option>
		{foreach from=$area_list item=area}
			<option value="{$area}" {if $smarty.request.area eq $area}selected {/if}>{$area}</option>
		{/foreach}
	{else}
		<option value="NO_DATA">-- No Data --</option>
	{/if}
</select>