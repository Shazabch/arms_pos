{*
5/11/2015 2:34 PM Andy
- Remove the coupon by percentage.

10/30/2015 4:53 PM DingRen
- fix date display wrongly

4/21/2017 10:43 AM Khausalya
- Enhanced changes from RM to use config setting. 

8/27/2019 1:54 PM Andy
- Added Discount by Percentage.
- Added Minimum Receipt Amount.
*}

<div style="padding:10;">
	<h3>Print Coupon</h3>
	<form name="f_prn" method="get" onsubmit="return false;">
	<input type="hidden" name="id" value="{$form.id}">
	<input type="hidden" name="branch_id" value="{$form.branch_id}">
	<input type="hidden" name="discount_by" value="{$form.discount_by}">
	<input type="hidden" name="is_print" value="{$form.is_print}">

	<input type="hidden" name="a" value="print_coupon">
	<table>
		<tr>
			<td rowspan="12">
				<img src="ui/print64.png" hspace="11" align="left">
			</td>
			<td colspan="2">
				<h3>Print Options</h3>
			</td>
		</tr>
		<tr>
			<td>
				<b>Date:</b> 
			</td>
			<td>
				<span id="print_date_start">{$form.valid_from}</span> ~ <span id="print_date_end">{$form.valid_to}</span>
			</td>
		</tr>
		<tr>
			<td>
				<b>Time:</b> 
			</td>
			<td>
				<span id="print_time_start">{$form.valid_time_from}</span> ~ <span id="print_time_end">{$form.valid_time_to}</span>
			</td>
		</tr>
		<tr>
			<td>
				<b>Department:</b> 
			</td>
			<td>
				<span id="print_department">{$form.dept_desc}</span>
			</td>
		</tr>
		<tr id="print_brand" style="display:none">
			<td>
				<b>Brand:</b> 
			</td>
			<td>
				<span id="print_brand_desc">{$form.brand_desc|default:"-"}</span>
			</td>
		</tr>
		<tr id="print_vendor" style="display:none">
			<td>
				<b>Vendor:</b> 
			</td>
			<td>
				<span id="print_vendor_desc">{$form.vendor_desc|default:"-"}</span>
			</td>
		</tr>
		<tr>
			<td>
				<b>Code:</b> 
			</td>
			<td>
				<span id="print_code">{$form.code|default:"-"}</span>
			</td>
		</tr>
		<tr>
			<td>
				<b>Discount By:</b>
			</td>
			<td>
				{if $form.discount_by eq 'per'}
					Percentage
				{else}
					Amount
				{/if}
			</td>
		</tr>
		{*<tr {if !$config.coupon_use_percentage} style='display:none;' {/if}>
			<td>
				<b>Discount by:</b>
				<input name="print_type" type="radio" id='print_type_amount' value='amount' {if !$smarty.request.print_type || !$config.coupon_use_percentage || $smarty.request.print_type eq 'amount'} checked {/if} onclick="COUPON_DIALOG.toggle_amount_percentage();" > <label for='print_type_amount'>Amount</label>
				{if $config.coupon_use_percentage}
				<input name="print_type" type="radio" id='print_type_percentage' value='percentage' {if $smarty.request.print_type eq 'percentage'} checked {/if} onclick="COUPON_DIALOG.toggle_amount_percentage();" > <label for='print_type_percentage'>Percentage</label>
				{/if}
			</td>
		</tr>
		<tr id="use_percentage_id" style='display:none;'>
			<td>
				<b>Percentage(%):</b> <input id="print_percentage" name="print_percentage" size="10" maxlength="5" onchange="this.value=round2(this.value);">
				<sup>*Maximum percentage: 99.99% (Will get 1 cent different issue)</sup>
			</td>
		</tr>*}
		
		{if $form.discount_by eq 'per'}
			<tr>
				<td valign="top">
					<b>Percentage:</b> 
				</td>
				<td>
					<input id="print_value" name="print_value" size="10" maxlength="5" onchange="this.value=round2(this.value);"><br />
					<sup>*Maximum percentage: 99.99% (Will get 1 cent different issue)</sup>
				</td>
			</tr>
		{else}
			<tr id="use_amount_id">
				<td valign="top">
					<b>Amount:</b> 
				</td>
				<td>
					<input id="print_value" name="print_value" size="10" maxlength="6" onchange="this.value=round2(this.value);"><br />
					<sup>*Maximum amount: {$config.arms_currency.symbol} 999.9{if $config.coupon_amount_0_5_cent}5{else}9{/if}</sup>
				</td>
			</tr>
		{/if}
		
		<tr>
			<td valign="top">
				<b>Quantity:</b> 
			</td>
			<td>
				<input id="print_qty" name="print_qty" size="10" maxlength="3" onchange="this.value=int(this.value);"><br />
				<sup>*Maximum quantity to be printed: 500 pieces</sup>
			</td>
		</tr>
		<tr>
			<td>
				<b>Format:</b>
			</td>
			<td>
				<select name='print_format'>
					{foreach from=$print_format key=id item=format}
					<option value="{$id}" {if $smarty.request.print_format eq $id} selected {/if} >{$format.description}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<b>Remark:</b>
			</td>
			<td>
				<input name='remark' value="" maxlength="100" size="30">
			</td>
		</tr>
	</table>
	<span style="color:red;">* Imprtant: once printed, you will not allow to change the Coupon Details anymore.</span>
	<p align="center">
		<input type="button" value="Print" onClick="COUPON_DIALOG.print_ok();"> <input type="button" value="Cancel" onclick="COUPON_DIALOG.curtain_clicked();">
	</p>

	</form>
</div>
