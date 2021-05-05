{*
7/5/2010 4:41:52 PM Andy
- Fix counter collection adjustment bugs, when adjustment value more than 999 system will save wrong value.
- Make foreign currency payment type cannot do adjustment.

3/21/2014 4:14 PM Justin
- Enhanced to show custom payment type label if found it is set.

06/09/2016 14:30 Edwin
- Reconstruct on payment type

3/3/2017 11:57 AM Andy
- Enhanced to not allow to choose global "Credit Cards", must choose one of its child or others.

7/11/2017 4:18 PM Justin
- Bug fixed on number format did not remove the comma "," and causes system saves 1 digit of figure.

7/19/2017 4:40 PM Justin
- Bug fixed on amount will still allow user to key in comma "," and save the adjustment.

7/13/2018 3:08 PM Justin
- Enhanced the drop down list to have foreign currency selection.

4/16/2019 6:04 PM Justin
- Enhanced to disable delete, change payment type, modify remark and amount while it is eWallet payment.
*}

<tr id="tr_{$smarty.foreach.j.iteration|default:$i}_{$pos_id|default:$item.id}">
	<td style="border:0px;">
		{if preg_match('/^ewallet_/i', $item.type)}
			<img src="/ui/pixel.gif" height="17" align=absmiddle>
		{else}
			<img src="/ui/rejected.png" align=absmiddle onclick="del_payment_type(this, {$pos_id|default:$item.id});">
		{/if}
		<input name=pos_id[] value="{$item.id}" type=hidden>
		<input class=receipt_no name="receipt_no[{$item.id}]" value="{$item.receipt_no}" type=hidden>
		Type:
		
		{if preg_match('/^ewallet_/i', $item.type)}
			<input type="text" value="{$item.type}" name="type[{$item.id}][{$item.payment_id}]" size="16" readonly />
		{else}
			<select name="type[{$item.id}][{$item.payment_id}]" onchange="check_cash_credit(this, {$item.receipt_no}, {$pos_id|default:$item.id}, {$item.payment_id});" class="sel_payment_type">
			{foreach from=$pos_config.payment_type item=paytype}
				<option value="{$paytype}" {if $item.type eq $paytype}selected{/if} {if $paytype eq 'Credit Cards'}disabled {/if}>
					{if $paytype eq "Currency_Adjust"}
						Currency Adjust
					{else}
						{$paytype}
					{/if}
				</option>
				{if $paytype eq 'Credit Cards'}
					{foreach from=$pos_config.credit_card item=c}
						<option value="{$c}" {if $item.type eq $c or ($item.type eq 'Credit Cards' and $c eq 'Others')}selected {/if}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$c}</option>
					{/foreach}
				{/if}
			{/foreach}
			{if $foreign_currency_list}
				<option value="Foreign Currency" disabled>Foreign Currency</option>
				{foreach from=$foreign_currency_list item=curr_code}
					<option value="{$curr_code}" {if $item.type eq $curr_code}selected {/if}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$curr_code}</option>
				{/foreach}
			{/if}
			</select>
		{/if}
	</td>
	<td style="border:0px;">
		<span>Remark: <input name="remark[{$item.id}][{$item.payment_id}]" value="{$item.remark}" {if preg_match('/^ewallet_/i', $item.type)}readonly{/if}></span>
	</td>
	<td style="border:0px;">
		<input id="amount_{$item.id}_{$item.payment_id}" name="amount[{$item.id}][{$item.payment_id}]" value="{$item.payment_amount|number_format:2:'.':''}" onchange="mf(this);" style="text-align: right" size="5" {if preg_match('/^ewallet_/i', $item.type)}readonly{/if} />
	</td>
</tr>


{*
<tr id=tr_{$smarty.foreach.j.iteration|default:$i}_{$receipt_no|default:$item.receipt_no}>
<td style="border:0px;">{if $item.type eq 'Coupon' or $item.type eq 'Voucher'}<img src="/ui/rejected.png" align=absmiddle onclick="del_coupon({$smarty.foreach.j.iteration|default:$i},{$receipt_no|default:$item.receipt_no});">{/if}
<input name=pos_id[] value="{$item.id}" type=hidden>
<input class=receipt_no name=receipt_no[] value="{$item.receipt_no}" type=hidden>
{assign var=pp_can_edit value=1}


{if $item.type eq 'Cash' or in_array($item.type,$cc) or $item.type eq 'Check'}
Type: <select name="type[{$item.receipt_no}][{$item.payment_id}]" onchange="check_cash_credit(this,{$item.receipt_no},{$item.payment_id});">
	{foreach from=$cc item=paytype}
		<option value="{$paytype}" {if $paytype eq $item.type}selected{/if}>{$pos_config.payment_type_label.$paytype|default:$paytype}</option>
	{/foreach}
	
{elseif $item.type eq 'Coupon' or $item.type eq 'Voucher'}
Type: <select name="type[{$item.receipt_no}][{$item.payment_id}]">
	{foreach from=$coupon_voucher item=paytype}
		<option value="{$paytype}" {if $paytype eq $item.type}selected{/if}>{$pos_config.payment_type_label.$paytype|default:$paytype}</option>
	{/foreach}
{elseif $extra_payment_type and in_array($item.type, $extra_payment_type)}
Type: <select name="type[{$item.receipt_no}][{$item.payment_id}]">
	{foreach from=$extra_payment_type item=paytype}
		<option value="{$paytype}" {if $paytype eq $item.type}selected{/if}>{$pos_config.payment_type_label.$paytype|default:$paytype}</option>
	{/foreach}
{else}
{assign var=pp_can_edit value=0}
{assign var=pt value=$item.type}
Type: <select disabled><option>{$pos_config.payment_type_label.$pt|default:$pt}</option>
<input type="hidden" name="type[{$item.receipt_no}][{$item.payment_id}]" value="{$item.type}" />
{/if}

</select>
</td>
<td style="border:0px;">
<span>Remark: <input name="remark[{$item.receipt_no}][{$item.payment_id}]" value="{$item.remark}" {if !$pp_can_edit}readonly {/if}></span>
</td>
<td style="border:0px;"><input id=amount_{$item.receipt_no}_{$item.payment_id} name=amount[{$item.receipt_no}][{$item.payment_id}] value="{$item.payment_amount}" {if in_array($item.type,$cc) or !$pp_can_edit}readonly {/if} size="5" />
</td>
</tr>
*}