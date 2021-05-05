{*
5/20/2010 3:20:38 PM Andy
- Add sorting for location and shelf range.
- Shelf range change to only show those between selected location.

7/23/2010 4:59:47 PM Andy
- Add single server mode and hq can create stock take for branch.
- Fix stock take item list if open multiple tab will cause bugs.

8/19/2010 3:33:21 PM Alex
- Add SKU type filter

9/7/2010 6:10:59 PM Alex
- add sku scan at main page and stock count sheet no.

06/25/2020 04:08 PM Sheila
- Updated button css
*}

<fieldset style="width: 300px;">
	<legend><b>Select By Range</b></legend>
	<table>
		<tr>
			<td>Location From</td>
			<td>
				<div id=div_location2>
					<select name=loc2 onChange="reload_shelf_range();">
						{foreach from=$loc item=val}
							<option value="{$val.location}" {if $smarty.request.location eq $val.location}selected {/if}>{$val.location|upper}</option>
						{/foreach}
					</select>
				</div>
			</td>
			<td>To</td>
			<td>
				<div id=div_location3>
					<select name=loc3 onChange="reload_shelf_range();">
						{foreach from=$loc item=val}
							<option value="{$val.location}" {if $smarty.request.location eq $val.location}selected {/if}>{$val.location|upper}</option>
						{/foreach}
					</select>
				</div>
			</td>
			<td id="td_loading_shelf_range"></td>
		</tr>
		<tr id="tr_shelf_range">
			{include file='admin.stock_take.range.shelf.tpl'}
		</tr>
		<tr>
			<td>Sku Type</td>
			<td>
				<select name='p_sku_type'>
				    <option value=''>All</option>
				    {foreach from=$sku_type item=pcode}
				    <option value='{$pcode.code}' {if $smarty.request.p_sku_type eq $pcode.code} selected {/if} >{$pcode.code}</option>
				    {/foreach}
				</select>
			</td>
		</tr>
		{if $config.stock_take_count_sheet}
		<tr>
		    <td>Stock Count Sheet No.</td>
		    <td><input name='count_sheet' type=text maxlength=5 size=10 value='{$smarty.request.count_sheet}' onChange="miz(this);" /></td>
		</tr>
		{/if}
	</table>
	<br>
	<input class="btn btn-primary" type=button value="Print Check List" onclick="print_sheet('sheet')">&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="print_with_qty" value=1> Print with quantity
</fieldset>
