<h3>Currency Code: {$form.code}</h3>

* Leave New Rate as empty and system will not update it.
<form name="f_fc" method="post" onSubmit="return false;">
	<input type="hidden" name="a" value="update_currency" />
	<input type="hidden" name="curr_code" value="{$form.code}" />
	
	
	<table width="100%" class="report_table">
		<tr class="header">
			<th width="80%">&nbsp;</th>
			<th>
				Rate <sup class="rate_number">1</sup><br />
				(Foreign to {$config.arms_currency.code})<br />
				[<a href="javascript:void(alert('{$LANG.FOREIGN_CURRENCY_RATE_NOTICE|escape:javascript}'));">?</a>]
			</th>
			<th>
				Rate <sup class="rate_number">2</sup><br />
				({$config.arms_currency.code} to Foreign)<br />
				[<a href="javascript:void(alert('{$LANG.FOREIGN_CURRENCY_BASE_RATE_NOTICE|escape:javascript}'));">?</a>]
			</th>
		</tr>
		
		{* Current Rate *}
		<tr>
			<th align="left">Current Rate</th>
			
			{* Rate *}
			<td align="right">
				<input type="text" name="old_rate" value="{$form.rate}" class="r" size="15" readonly />
			</td>
			
			{* Base Rate *}
			<td align="right">
				<input type="text" name="old_base_rate" value="{$form.base_rate}" class="r" size="15" readonly />
			</td>
		</tr>
		
		{* New Rate *}
		<tr valign="top">
			<th align="left">
				New Rate
			</th>
			
			{* Rate *}
			<td align="right">
				<input type="text" name="new_rate" value="" class="r" onchange="CURRENCY_RATE_EDIT_DIALOG.rate_changed('rate');" size="15" />
			</td>
			
			{* Base Rate *}
			<td align="right" nowrap>
				<span id="span_converted_base_rate_low" style="display:none;">
					<img src="ui/icons/error.png" align="absmiddle" onClick="alert('Rate 2 Exchange Rate is lower than Rate 1.');" />
				</span>
				<input type="text" name="new_base_rate" value="" class="r" onchange="CURRENCY_RATE_EDIT_DIALOG.rate_changed('base_rate');" size="15" />
				<br />
				<span id="span_converted_base_rate_notice"></span>
				
			</td>
		</tr>
	</table>
	
	<p align="center">
		<input type="button" value="Update" onclick="CURRENCY_RATE_EDIT_DIALOG.update_currency();" />
	</p>
</form>