{*
4/27/2015 5:07 PM Justin
- Enhanced to have Currency Description.
*}

<fieldset width="50%">
	<legend><b><font size="2">Currency List</font></b></legend>
	<table width="100%" style="border:1px solid #999; padding:1px;" class="input_no_border body" border=0 cellspacing=1 cellpadding=1>
		<tr bgcolor="#ffee99">
			<th>Currency Code</th>
			<th>Currency Description</th>
			<th width="40%">Exchange Rate</th>
		</tr>
	<tbody id="currency_details">
		{foreach from=$currency_list item=curr_type key=curr_id name=currency}
			<tr id="currency_code_{$curr_type}">
				<input type="hidden" value="{$curr_type}" name="currency_code[{$curr_type}]" currency_code="{$curr_type}" class="currency_code">
				<td>{$curr_type}</td>
				<td><input type="text" name="currency_description[{$curr_type}]" value="{$forex_info.$curr_type.currency_description}"></td>
				<td align="center">
					<input type="text" name="exchange_rate[{$curr_type}]" onchange="this.value=float(this.value);" value="{$forex_info.$curr_type.exchange_rate|default:0}" class="r">&nbsp;
					<img title="View History" onclick="get_currency_history('{$curr_type}')" src="/ui/icons/zoom.png">
				</td>
			</tr>
		{/foreach}
		<tr>
			<td colspan="3" align="center" id="no_data" {if count($currency_list) > 0}style="display:none;"{/if}>No Currency Code found.</td>
		</tr>
	</tbody>
	</table>
	<div id="save_area" {if count($currency_list) eq 0}style="display:none;"{/if} align="center">
		<br />
		<input type="submit" value="Save">
	</div>
</fieldset>
