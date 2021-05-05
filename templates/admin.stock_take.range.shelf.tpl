{*
5/20/2010 3:20:38 PM Andy
- Add sorting for location and shelf range.
- Shelf range change to only show those between selected location.

7/23/2010 4:59:47 PM Andy
- Add single server mode and hq can create stock take for branch.
- Fix stock take item list if open multiple tab will cause bugs.

*}

<td>Shelf From</td>
<td>
	<div id=div_shelf2>
		<select name=shelf2>
			{foreach from=$shelf item=val}
				<option value="{$val.shelf}" {if $smarty.request.shelf eq $val.shelf}selected {/if}>{$val.shelf}</option>
			{/foreach}
		</select>
	</div>
</td>
<td>To</td>
<td>
	<div id=div_shelf3>
		<select name=shelf3>
			{foreach from=$shelf item=val}
				<option value="{$val.shelf}" {if $smarty.request.shelf eq $val.shelf}selected {/if}>{$val.shelf}</option>
			{/foreach}
		</select>
	</div>
</td>
