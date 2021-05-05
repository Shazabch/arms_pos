{*
4/21/2017 8:48 AM Khausalya 
- Enhanced changes from RM to use config setting. 

6/13/2017 15:58 Qiu Ying
- Bug fixed on qty values are listed with currency symbols

06/29/2020 11:31 AM Sheila
- Updated button css.
*}
{foreach from=$items.$date_item_id item=saci}
	<tr valign="top" id="sac_item_row_{$saci.id}" {if $is_hidden || $saci.is_deleted}style="display:none;"{/if}>
		<td bgcolor="#DDDDDD" align="center">
			<a onclick="SA_COMMISSION_MODULE.toggle_commission_item_status({$saci.id});">
				<img src="{if $saci.active}ui/deact.png{else}ui/act.png{/if}" id="img_sac_item_status_{$saci.id}" title="{if $saci.active}Deactivate{else}Activate{/if} this Commission Item" border="0">
			</a>
			<a onclick="SA_COMMISSION_MODULE.delete_commission_item({$saci.id}, {$date_item_id});"><img src="ui/icons/delete.png" title="Delete this Commission Item" border="0"></a>
			<span class="small" id="span_sac_item_inactive_{$saci.id}" {if $saci.active}style="display:none;"{/if}><br />(inactive)</span>
			<input type="hidden" name="date_group_id[{$saci.id}]" value="{$date_item_id}">
			<input type="hidden" name="date_to[{$saci.id}]" value="{$saci.date_to}">
			<input type="hidden" name="sku_item_id[{$saci.id}]" value="{$saci.sku_item_id}">
			<input type="hidden" name="category_id[{$saci.id}]" value="{$saci.category_id}">
			<input type="hidden" name="brand_id[{$saci.id}]" value="{$saci.brand_id}">
			<input type="hidden" name="sku_type[{$saci.id}]" value="{$saci.sku_type}">
			<input type="hidden" name="vendor_id[{$saci.id}]" value="{$saci.vendor_id}">
			<input type="hidden" name="is_deleted[{$saci.id}]" value="0" class="is_deleted_{$date_item_id}">
			<input type="hidden" name="active[{$saci.id}]" value="{$saci.active}">
		</td>
		<td bgcolor="#DDDDDD">
			{if $saci.sku_item_id}
				<b>SKU: </b>{$saci.sku_item_code} / {$saci.artno} / {$saci.description}
			{else}
				{if $saci.cat_desc}
					<b>Category:</b> {$saci.cat_desc}
				{/if}
				{if $saci.brand_desc}
					{if $saci.cat_desc}<br />+<br />{/if}
					<b>Brand:</b> {$saci.brand_desc}
				{/if}
			{/if}
		</td>
		<td bgcolor="#DDDDDD">
			{if $saci.sku_type}
				<b>SKU Type: </b>
				{$saci.sku_type}
			{/if}
			{if is_array($saci.price_type)}
				{if $saci.sku_type}<br />+<br />{/if}
				<b>Price Type: </b>
				{foreach from=$saci.price_type item=pt key=pt_code name=pt_list}
					<input type="hidden" name="price_type[{$saci.id}][]" value="{$pt_code|trim}">
					{$pt_code|trim}{if !$smarty.foreach.pt_list.last},{/if}
				{/foreach}
			{/if}
			{if $saci.vendor_desc}
				{if $saci.sku_type || is_array($saci.price_type)}<br />+<br />{/if}
				<b>Vendor: </b>
				{$saci.vendor_desc}
			{/if}
		</td>
		<td bgcolor="#DDDDDD" nowrap>
			<b>Type: </b>
			<select name="commission_method[{$saci.id}]" onchange="SA_COMMISSION_MODULE.commission_method_changed(this, {$saci.id});">
				{foreach from=$commission_type item=ct key=r name=pt_list}
					<option value="{$ct}" {if $ct eq $saci.commission_method}selected{/if}>{$ct}</option>
				{/foreach}
			</select>
			<span id="span_cm_range_field_{$saci.id}" {if $saci.commission_method eq "Flat"}style="display:none;"{/if}>
				<b>From</b> <input type="text" class="r" name="cm_range_from[{$saci.id}]" size="7" value="{$saci.flat_value}" onchange="{if $saci.commission_method eq 'Sales Range'}mf(this);{else}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{/if}"> 
				<b>To</b> <input type="text" class="r" name="cm_range_to[{$saci.id}]" size="7" value="{$saci.flat_value}" onchange="{if $saci.commission_method eq 'Sales Range'}mf(this);{else}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{/if}"><b> = </b>
			</span>
			<input type="text" class="r" name="cm_value[{$saci.id}]" size="5" value="{if $saci.commission_method eq 'Flat'}{$saci.commission_value}{/if}" onchange="SA_COMMISSION_MODULE.check_cm_value(this);">
			<span id="span_cm_range_table_{$saci.id}" {if $saci.commission_method eq "Flat"}style="display:none;"{/if}>
				<button class="btn btn-primary" name="range_btn" onclick="SA_COMMISSION_MODULE.add_commission_method_range({$saci.id}); return false;">Add</button>
				<table width="100%">
					<tr>
						<th bgcolor="#CACACA" width="5%">&nbsp;</th>
						<th bgcolor="#B6B6B6" width="65%">Range</th>
						<th bgcolor="#B6B6B6" width="30%">Value</th>
					</tr>
					<tbody class="sac_item_cm_range_{$saci.id}" id="sac_item_cm_range_{$saci.id}">
						{assign var=range_count value=0}
						{if $saci.commission_method ne "Flat" && $saci.commission_value}
							{if $saci.commission_method eq "Sales Range"}
								{assign var=amt_label value=$config.arms_currency.symbol}
							{else}
								{assign var=amt_label value=""}
							{/if}
							{foreach from=$saci.commission_value item=cv key=r name=cv_list}
								<tr class="sac_item_cm_range_row_{$saci.id}" id="sac_item_cm_range_row_{$saci.id}_{$r}">
									<td bgcolor="#CACACA">
										<img src="/ui/del.png" width="15" align="absmiddle" onclick="SA_COMMISSION_MODULE.delete_commission_method_range({$saci.id}, {$r});" class="clickable"/>
										<input type="hidden" name="sac_item_cm_range_from[{$saci.id}][{$r}]" value="{$cv.range_from}">
										<input type="hidden" name="sac_item_cm_range_to[{$saci.id}][{$r}]" value="{$cv.range_to}">
									</td>
									<td bgcolor="#CACACA">
										<span id="span_sac_item_cm_range_{$saci.id}_{$r}">
											{if $cv.range_from > 0 && $cv.range_to > 0}
												Between {$amt_label}{$cv.range_from} - {$amt_label}{$cv.range_to}
											{elseif $cv.range_from > 0 && $cv.range_to eq 0}
												Start from {$amt_label}{$cv.range_from}
											{elseif $cv.range_from eq 0 && $cv.range_to > 0}
												At most {$amt_label}{$cv.range_to}
											{/if}
										</span>
									</td>
									<td align="right" bgcolor="#CACACA">
										{$cv.value}
										<input type="hidden" name="sac_item_cm_range_value[{$saci.id}][{$r}]" value="{$cv.value}">
									</td>
								</tr>
								{if $smarty.foreach.cv_list.last}{assign var=range_count value=$r}{/if}
							{foreachelse}
								<tr id="cm_range_no_data_{$saci.id}">
									<td colspan="3" bgcolor="#CACACA" align="center">No Record</td>
								</tr>
							{/foreach}
						{else}
							<tr id="cm_range_no_data_{$saci.id}">
								<td colspan="3" bgcolor="#CACACA" align="center">No Record</td>
							</tr>
						{/if}
						<input type="hidden" name="sac_item_cm_range_count[{$saci.id}]" value="{$range_count|default:0}">
					</tbody>
				</table>
			</span>
		</td>
	</tr>
{/foreach}