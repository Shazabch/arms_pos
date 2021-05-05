{*
3/24/2015 9:57 AM Justin
- Enhanced to have mprice selection.

3/27/2015 2:48 PM Justin
- Enhanced to have normal price choice on mprice.
*}

{if $config.sku_multiple_selling_price && $form.mprice}
	{foreach from=$config.sku_multiple_selling_price item=s}
		{if in_array($s, $form.mprice)}
			{assign var=sp_type_count value=$sp_type_count+1}
		{/if}
	{/foreach}
{/if}

<p>
<font color="blue"><b>* System have found {$items|@count} item(s).</b></font><br />
{if $gst_setting_info.setting_value}
	<font color="blue"><b>* All prices is rounding in condition of {$gst_setting_info.setting_value}.</b></font>
{else}
	<font color="blue"><b>* Rounding condition is not set, <a href="masterfile_gst_settings.php" target="_blank">click here</a> to set.</b></font>
{/if}
</p>
<table width="100%" id="tbl_item" style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing="1" cellpadding="4">
	<tr height="32" bgcolor="#ffffff" class="small">
		<th rowspan="2">#</th>
		<th rowspan="2">Artno /<br />Mcode</th>
		<th rowspan="2">ARMS</th>
		<th rowspan="2">Description</th>
		<th rowspan="2">Stock<br />Balance</th>
		<th rowspan="2">Current<br />Selling Price</th>
		{if $config.sku_multiple_selling_price && $form.mprice.normal}
		<th colspan="3">Normal Selling Price</th>
		{/if}
		{if $config.sku_multiple_selling_price && $form.mprice}
			<th colspan="{$sp_type_count}">Multiple Selling Prices</th>
		{/if}
	</tr>
	<tr height="32" bgcolor="#ffffff" class="small">
		{if $config.sku_multiple_selling_price && $form.mprice.normal}
			<th>Price</th>
			<th>GST</th>
			<th>Before GST</th>
		{/if}
		{if $config.sku_multiple_selling_price && $form.mprice}
			{foreach from=$config.sku_multiple_selling_price item=s}
				{if in_array($s, $form.mprice)}
					<th>{$s}</th>
				{/if}
			{/foreach}
		{/if}
	</tr>

	<tbody id="items">
	{foreach from=$items item=item name=fitem}
		<tr>
			<td>
				{$smarty.foreach.fitem.iteration}.
				<input type="hidden" name="inclusive_tax[{$item.id}]" value="{$item.inclusive_tax}" />
				<input type="hidden" name="gst_rate[{$item.id}]" value="{$item.gst_rate}" />
				<input type="hidden" name="original_price[{$item.id}]" value="{$item.selling_price}" />
				<input type="hidden" name="sku_item_code[{$item.id}]" value="{$item.sku_item_code}" />
			</td>
			<td>{$item.artno} {if $item.mcode}/<br />{$item.mcode}{/if}</td>
			<td>{$item.sku_item_code}</td>
			<td>{$item.description}</td>
			<td class="r">{$item.stock_bal}</td>
			<td class="r">{$item.selling_price|number_format:2}</td>
			{if $config.sku_multiple_selling_price && $form.mprice.normal}
				<td align="center">
					<!-- normal selling price -->
					<input type="text" name="price[{$item.id}][normal]" class="r" value="{$item.proposed_selling_price|number_format:2:'.':''}" onchange="mf(this); GST_PRICE_WIZARD_MODULE.calculate_gst({$item.id}, 'normal', this);" size="5" />
				</td>
				<td align="center">
					{assign var=selling_price value=$item.proposed_selling_price}
					{if $item.inclusive_tax eq "yes"}
						{assign var=tmp_gst_rate value=$item.gst_rate+100}
						{assign var=gst_selling_price value=$selling_price*100/$tmp_gst_rate}
						{assign var=gst_amt value=$gst_selling_price*$item.gst_rate/100}
					{else}
						{assign var=gst_amt value=$selling_price*$item.gst_rate/100}
						{assign var=gst_selling_price value=$selling_price+$gst_amt}
					{/if}
					{assign var=gst_selling_price value=$gst_selling_price|round:2}
				
					<!-- GST amount -->
					<input type="text" name="gst_amount[{$item.id}][normal]" class="r" value="{$gst_amt|number_format:2:'.':''}" size="5" readonly />
					<font color="blue"><b>({$item.gst_rate}%)</b></font>
				</td>
				<td align="center">
					<!-- GST selling price -->
					<input type="text" name="gst_price[{$item.id}][normal]" class="r" value="{$gst_selling_price|number_format:2:'.':''}" onchange="mf(this); GST_PRICE_WIZARD_MODULE.calculate_gst({$item.id}, 'normal', this);" size="5" />
				</td>
			{/if}
			{if $config.sku_multiple_selling_price && $form.mprice}
				{foreach from=$config.sku_multiple_selling_price item=s}
					{if in_array($s, $form.mprice)}
						<td align="right">
							<input type="text" name="price[{$item.id}][{$s}]" class="r mprice_{$item.id}" value="{$item.proposed_mprice.$s|number_format:2:'.':''}" onchange="mf(this);" size="5" />
							<div>
								<font size="1" color="blue"><b>{$item.mprice.$s|number_format:2}</b></font>
							</div>
						</td>
					{/if}
				{/foreach}
			{/if}
		</tr>
	{foreachelse}
		<td colspan="{$sp_type_count+8}" align="center">-- No Data -- </td>
	{/foreach}
	</tbody>
</table>

<br />

{if $items}
<div align="center">
{if $sessioninfo.privilege.MST_SKU_UPDATE_FUTURE_PRICE}
<input type="button" name="toggle_future_price_btn" value="Generate As Batch Price Change" onclick="GST_PRICE_WIZARD_MODULE.toggle_settings_window('future_price');" style="font:bold 20px Arial; background-color:#f90; color:#fff;" />
{/if}
{if $sessioninfo.privilege.MST_SKU_UPDATE_PRICE}
<input type="button" name="toggle_change_price_btn" value="Update As Price Change" onclick="GST_PRICE_WIZARD_MODULE.toggle_settings_window('change_price');" style="font:bold 20px Arial; background-color:#f90; color:#fff;" />
{/if}
</div>
{/if}