{*
3/29/2012 4:54:32 PM Justin
- Enhanced the UOM table to have Ctn #1 and #2.
- Re-aligned to adjust both tables Sales Trend and Item Info.

10/3/2017 10:33 AM Justin
- Bug fixed the qty and ratio from sales trend table always rounded up instead of having decimal points.

12/14/2020 1:22 PM Rayleen
- Add additional description column 
- Additional description - if more than 5 lines, show first 5 lines then add 'Show more' link to view other lines

12/22/2020 5:46 PM Rayleen
- Add SKU Details Column
- Display Color/Size, if 'Show More Info' is clicked, show other details like flavour, weight, etc.
*}

<table border=0 cellpadding="3">
<tr>
	<td><b>Sales Trend</b></td>
	<td><b>Item Info</b></td>
	{if $sb_info.additional_description }
	<td><b>Additional Description</b></td>
	{/if}
	<td><b>SKU Details</b></td>
</tr>
<tr>
	<td valign="top">
		<table class="input_no_border small body" cellspacing="1" cellpadding="1" style="border: 1px solid rgb(153, 153, 153); padding: 5px;height:75px; background-color: rgb(255, 238, 153);">
			<tr bgcolor="#ffffff">
				<th nowrap style="background:#ccc;">
					<span style="border:1px solid #ccc;">1M</span>
				</th>
				<th nowrap style="background:#ddd;">
					<span style="border:1px solid #ccc;background:#ddd">3M</span>
				</th>
				<th nowrap style="background:#ccc;">
					<span style="border:1px solid #ccc;background:#ccc">6M</span>
				</th>
				<th nowrap style="background:#ddd;">
					<span style="border:1px solid #ccc;">12M</span>
				</th>
				{*<th>System Stock</th>*}
			</tr>
			<tr bgcolor="#ffffcc">
				<td align=center nowrap>
					<input name="sales_trend[qty][1]" size=5 style="width:30px;background:#ccc;" value="{$item.sales_trend.qty.1|qty_nf:".":""|ifzero}" readonly>
				</td>
				<td align=center nowrap>
					<input name="sales_trend[qty][3]" style="width:30px; background:#ddd;" size=5 value="{$item.sales_trend.qty.3|qty_nf:".":""|ifzero}" readonly>
				</td>
				<td align=center nowrap>
					<input name="sales_trend[qty][6]" size=5 style="width:30px;background:#ccc;" value="{$item.sales_trend.qty.6|qty_nf:".":""|ifzero}" readonly>
				</td>
				<td align=center nowrap>
					<input name="sales_trend[qty][12]" style="width:30px; background:#ddd;" size=5 value="{$item.sales_trend.qty.12|qty_nf:".":""|ifzero}" readonly>
				</td>
			</tr>
				<td align=center nowrap>
					<input size=5 style="width:30px;background:#ccc;" value="{$item.sales_trend.qty.1|qty_nf:".":""|ifzero}" readonly>
				</td>
				<td align=center nowrap>
					<input style="width:30px; background:#ddd;" size=5 value="{$item.sales_trend.qty.3/3|qty_nf:".":""|ifzero}" readonly>
				</td>
				<td align=center nowrap>
					<input size=5 style="width:30px;background:#ccc;" value="{$item.sales_trend.qty.6/6|qty_nf:".":""|ifzero}" readonly>
				</td>
				<td align=center nowrap>
					<input style="width:30px; background:#ddd;" size=5 value="{$item.sales_trend.qty.12/12|qty_nf:".":""|ifzero}" readonly>
				</td>
				{*
				<td align="center">
					{$item.available_stock|number_format}
					<input type="hidden" name="system_stock" value="{$item.available_stock}" />
				</td>
				*}
			</tr>
		</table>
	</td>
	
	<td valign="top">
		<table class="input_no_border small body" cellspacing="1" cellpadding="1" style="border: 1px solid rgb(153, 153, 153); padding: 5px;height:75px; background-color: rgb(255, 238, 153);">
			<tr bgcolor="#ffffff">
				<th nowrap style="background:#ccc;">
					<span style="border:1px solid #ccc;">Packing <br />UOM Code</span>
				</th>
				{if $config.do_request_show_ctn_1_2}
					<th nowrap style="background:#ccc;">
						<span style="border:1px solid #ccc;">Ctn #1</span>
					</th>
					<th nowrap style="background:#ccc;">
						<span style="border:1px solid #ccc;">Ctn #2</span>
					</th>
				{/if}
			</tr>
			<tr bgcolor="#ffffcc">
				<td align=center nowrap style="border:2px inset #aaa ;width:50px;background:#ccc;">
					{$sb_info.uom_code|default:'EACH'}
				</td>
				{if $config.do_request_show_ctn_1_2}
					<td align=center nowrap style="border:2px inset #aaa ;width:50px;background:#ccc;">
						{$sb_info.ctn1_uom_code|default:'EACH'}
					</td>
					<td align=center nowrap style="border:2px inset #aaa ;width:50px;background:#ccc;">
						{$sb_info.ctn2_uom_code|default:'EACH'}
					</td>
				{/if}
			</tr>
		</table>
	</td>
	{ if $sb_info.additional_description }
	<td valign="top">
		<div style="border: 1px solid rgb(153, 153, 153); padding: 2px;min-height: 100px;">
		{foreach from=$sb_info.additional_description key=key item=d}
			{if $key<5}
				{assign var=hide value=0}
			{else}
				{assign var=hide value=1}
			{/if}
			<span class="{if $hide}adesc{/if}" {if $hide}style="display:none"{/if}>{$d}<br></span>
		{/foreach}
		{if $hide}
			<input id="show_more_line" type="hidden" value="1" />
			<a style="cursor:pointer;float:right;font-size: 11px;" onclick="show_more_description();" id="show_more">Show More</a>
		{/if}
		<br style="clear: both;">
		</div>
	</td>
	{/if}
	<td valign="top">
		<div style="border: 1px solid rgb(153, 153, 153); padding: 2px;min-height: 100px;">
			<table style="min-width:200px; max-width: 300px;" cellpadding="2">
				<tr>
					<td width="70"><b>Color</b></td>
					<td>{$sb_info.color}</td>
				</tr>
				<tr>
					<td><b>Size</b></td>
					<td>{$sb_info.size|default:'0'}</td>
				</tr>
			</table>
			{assign var=display value=0}
			<table id="other_info" style="display:none; min-width:200px; max-width: 300px;" cellpadding="2">
				{foreach from=$sku_info key=index item=sku}
					{if $sku}
					{assign var=display value=$display+1}
					<tr>
						<td width="70"><b>{$index|replace:'_':' <br>'|ucfirst}</b></td>
						<td>{$sku}</td>
					</tr>
					{/if}
				{/foreach}
			</table>
			{if $display}
			<input id="show_more_item_info" type="hidden" value="1" />
			<a style="cursor:pointer;float:right;font-size: 11px;" onclick="show_more_info();" id="show_more_info" >Show More Info</a>
			{/if}
		<br style="clear: both;">
		</div>
	</td>
</tr>
</table>
