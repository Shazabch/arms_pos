
<tr id="sku_search_txt">
	<th align=left>Search SKU</th>
	<td>
		<input id="sku_item_id" name="sku_item_id" size=3 type=hidden>
		<input id="sku_item_code" name="sku_item_code" size=13 type=hidden>
		<input id="autocomplete_sku" name="sku" size=50 onclick="this.select()" style="font-size:14px;width:500px;">
		<div id=scan style="display:none"><input id=sku_scan name=sku_scan size=50 style="font-size:14px;width:500px;" onKeyPress="checkkey(event)"></div>
		<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	</td>
	<td><!--<input type=submit value="Find">--></td>
</tr>
<tr>
	<td nowrap><input type=checkbox name=handheld id=handheld onchange="load_handheld(this)">By Handheld</td>
	<td id=sku_search_chooice>
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="3"> ARMS Code
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="4"> Description
	</td>
</tr>
