{*
REVISION HISTORY
================

11/17/2009 12:10:56 PM edward
- change descrpition clear drawer to close counter.

7/5/2010 4:40:03 PM Andy
- Change when edit pos cash domination, if original currency rate is zero then will show default rate.

7/15/2010 10:26:57 AM Andy
- Counter Collection 3 add currency float.

3/11/2011 5:38:13 PM Andy
- Adjustment and increase the width of payment container to prevent them being overlap.
- Hide currency div if foreign currency is not set in pos settings.

5/4/2011 5:51:12 PM Justin
- Fixed the JS calculation for sub total not calculate during 1st time open.

10/15/2012 3:31:00 PM Fithri
- remove 'Discount' field from counter collection (Counter Collection Change X-Figure)

12/11/2012 3:33 PM Andy
- Add checking to payment type to show cash domination list.
- Change parseFloat() to use float() to fix NaN result.

2/25/2013 3:58 PM Fithri
- add button close.
- add checking if is add new cash denom, no key in any amount then cannot save
- can delete the cash denom create from backend

11/1/2013 2:32 PM Justin
- Bug fixed on the wrong calculation of Total Amount for original amount.

3/21/2014 3:35 PM Justin
- Modified the wording from "Check" to "Cheque".

4/27/2017 3:38 PM Justin
- Enhanced to use cash_domination_notes from config instead of pos_config.

6/20/2018 3:48 PM Justin
- Enhanced to load foreign currency list base on sales and config.

3/15/2019 5:21 PM Andy
- Enhanced Cash Denomination to have ewallet payment.
*}

{include file=header.tpl}

{literal}
<style>
.div_cd{
    float:left;
	width:320px;
	margin-right:10px;
}
</style>
{/literal}

<script type="text/javascript">
{literal}
function do_save(id)
{	
	if (id == '0' || id == '' || id == 'no_cd') { //only check for new one
		var all_inp = new Array();
		var passed = false;
		all_inp = $$('input.inp');
		for (i=0; i<all_inp.length; i++) {
			if (all_inp[i].value != '0' && all_inp[i].value != '') {
				passed = true;
				break;
			}
		}
		if (!passed) {
			alert('Please key in at least one Qty before save');
			return false;
		}
	}
	if (confirm('Are you sure?')) document.f_a.submit();
}

function do_delete() {
	if (confirm('Are you sure?')) {
		document.f_a.a.value = 'delete_cash_domination';
		document.f_a.submit();
	}
	else return false;
}

function calc_subtotal()
{
	var cash_total = 0;
	var inp = $$('input.inp');
	var total_amount = 0;
	var flt = 0;
	var total_other = 0;
	
	for(i=0;i<inp.length;i++)
	{
		id = inp[i].id;
		total = 0;
		
		if ($('vl_'+id) != undefined)
		{
			total = float($('vl_'+id).value)*inp[i].value;
			$('subtotal_'+id).innerHTML = total.toFixed(2);
			cash_total += total;
		}
		else if(inp[i].id == 'Float')
			flt += float(inp[i].value);
		else
			total_other += float(inp[i].value);
	}
	document.f_a.total_cash.value = $('total_cash').innerHTML = cash_total.toFixed(2);
	document.f_a.total_amount.value = $('total_amount').innerHTML = (cash_total - flt + total_other).toFixed(2);
}
{/literal}
</script>
{if $smarty.request.msg}{assign var=msg value=$smarty.request.msg}{/if}
<p align=center><font color=red>{$msg}</font></p>

<h1>{$PAGE_TITLE}</h1>
<form name=f_a method=post>
<input name=date value="{$smarty.request.date}" type="hidden">
<input name=counter_id value="{$smarty.request.counter_id}" type="hidden">
<input name=cashier_id value="{$smarty.request.cashier_id}" type="hidden">
<input name=id value="{$smarty.request.id}" type=hidden>
<input name=a value="save_cash_domination" type=hidden>
<input name=s value="{$smarty.request.s}" type=hidden>
<input name=e value="{$smarty.request.e}" type=hidden>
<div class="div_cd">
{assign var=total_cash value=0}
<table class="tb" width="100%" cellpadding=4 cellspacing=0 border=0>
<tr style="background:#fe9;">
<th>Notes</th>
<th>Qty</th>
<th>Sub Total</th>
<th>Original<br>Qty</th>
<th>Original<br>Subtotal</th>
</tr>
{foreach from=$config.cash_domination_notes key=note item=v}
	{if isset($item.data.$note) || isset($item.odata.$note) || $v.active}
		<tr>
			<td><b>{$v.label}</b></td>
			<td align=right><input class=inp id="{$note|escape:'html'}" size=3 name="data[{$note}]" value="{$item.data.$note|ifzero:0}" onchange="calc_subtotal()">
			<input id="vl_{$note|escape:'html'}" value="{$v.value}" type=hidden>
			</td>
			<td align="right" width="50" id="subtotal_{$note|escape:'html'}">&nbsp;</td>
			<td align="right">{$item.odata.$note|ifzero:0}</td>
			<td align="right">{$item.odata.$note*$v.value|ifzero:0}</td>
			{assign var=total_cash value=$total_cash+$item.odata.$note*$v.value}
		</tr>
	{/if}
{/foreach}
<tr>
<th colspan=2>Total Cash Notes</th>
<td id=total_cash align=right>&nbsp;</td>
<td>&nbsp;</td>
<td align=right>{$total_cash}</td>
</tr>
</table>
</div>

{* Center Div *}
<div class="div_cd">
{*assign var=total_amount value=0*}
<table class="tb" width="100%" cellpadding=4 cellspacing=0 border=0>
<tr style="background:#fe9;">
<th>Payment Type</th>
<th>Amount</th>
<th>Original Amount</th>
</tr>

{* Float *}
<tr>
	<td><b>Float</b></td>
	<td align=right><input style="text-align:right;" size=8 id=Float class=inp name=data[Float] value="{$item.data.Float|ifzero:0}" onchange="calc_subtotal()"></td>
	<td align=right>{$item.odata.Float|default:0|number_format:2}</td>
</tr>
{assign var=total_cash value=$total_cash-$item.odata.Float}

{* Check *}
{if !$normal_payment_type || in_array('Check', $normal_payment_type)}
	<tr>
		<td><b>Cheque</b></td>
		<td align=right><input style="text-align:right;" size=8 id=Check class=inp name=data[Check] value="{$item.data.Check|ifzero:0}" onchange="calc_subtotal()"></td>
		<td align=right>{$item.odata.Check|default:0|number_format:2}</td>
	</tr>
	{assign var=total_cash value=$total_cash+$item.odata.Check}
{/if}

{* Voucher *}
{if !$normal_payment_type || in_array('Voucher', $normal_payment_type)}
	<tr>
		<td><b>Voucher</b></td>
		<td align=right><input style="text-align:right;" size=8 id=Voucher class=inp name=data[Voucher] value="{$item.data.Voucher|ifzero:0}" onchange="calc_subtotal()"></td>
		<td align=right>{$item.odata.Voucher|default:0|number_format:2}</td>
	</tr>
	{assign var=total_cash value=$total_cash+$item.odata.Voucher}
{/if}

{*
<tr><td><b>Discount</b></td>
<td align=right><input style="text-align:right;" size=8 id=Discount class=inp name=data[Discount] value="{$item.data.Discount|ifzero:0}" onchange="calc_subtotal()"></td>
<td align=right>{$item.odata.Discount|default:0|number_format:2}</td>
</tr>
{assign var=total_cash value=$total_cash+$item.odata.Discount}
*}

{* Coupon *}
{if !$normal_payment_type || in_array('Coupon', $normal_payment_type)}
	<tr>
		<td><b>Coupon</b></td>
		<td align=right><input style="text-align:right;" size=8 id=Coupon class=inp name=data[Coupon] value="{$item.data.Coupon|ifzero:0}" onchange="calc_subtotal()"></td>
		<td align=right>{$item.odata.Coupon|default:0|number_format:2}</td>
	</tr>
	{assign var=total_cash value=$total_cash+$item.odata.Coupon}
{/if}

{* Debit *}
{if !$normal_payment_type || in_array('Debit', $normal_payment_type)}
	<tr>
		<td><b>Debit</b></td>
		<td align=right><input style="text-align:right;" size=8 id=Coupon class=inp name=data[Debit] value="{$item.data.Debit|ifzero:0}" onchange="calc_subtotal()"></td>
		<td align=right>{$item.odata.Debit|default:0|number_format:2}</td>
	</tr>
	{assign var=total_cash value=$total_cash+$item.odata.Debit}
{/if}

{* Credit Cards *}
{if !$normal_payment_type || in_array('Credit Cards', $normal_payment_type)}
	{foreach from=$pos_config.credit_card item=cc}
		<tr>
			<td><b>{$pos_config.payment_type_label.$cc|default:$cc}</b></td>
			<td align=right><input style="text-align:right;" class=inp size=8 name=data[{$cc}] value="{$item.data.$cc|ifzero:0}" onchange="calc_subtotal()"></td>
			<td align=right>{$item.odata.$cc|default:0|number_format:2}</td>
			{assign var=total_cash value=$total_cash+$item.odata.$cc}
		<tr>
	{/foreach}
{/if}

{* Extra Payment Type*}
{if $extra_cash_denom_type}
	<tr style="background:#fe9;">
		<th colspan="3">Custom Payment</th>
	</tr>
	{foreach from=$extra_cash_denom_type item=payment_type}
		<tr>
			<td><b>{$pos_config.payment_type_label.$payment_type|default:$payment_type}</b></td>
			<td align="right"><input style="text-align:right;" class="inp" size="8" name="data[{$payment_type}]" value="{$item.data.$payment_type|ifzero:0}" onchange="calc_subtotal()"></td>
			<td align="right">{$item.odata.$payment_type|default:0|number_format:2}</td>
			{assign var=total_cash value=$total_cash+$item.odata.$payment_type}
		</tr>
	{/foreach}
{/if}

{* eWallet *}
{if $ewallet_list}
	<tr style="background:#fe9;">
		<th colspan="3">eWallet</th>
	</tr>
	{foreach from=$ewallet_list item=payment_type}
		<tr>
			<td><b>{$payment_type}</b></td>
			<td align="right">
				<input style="text-align:right;" class="inp" size="8" name="data[{$payment_type}]" value="{$item.data.$payment_type|ifzero:0}" onchange="calc_subtotal()">
			</td>
			<td align="right">{$item.odata.$payment_type|default:0|number_format:2}</td>
			{assign var=total_cash value=$total_cash+$item.odata.$payment_type}
		</tr>
	{/foreach}
{/if}

<th>Total Amount</th>
<td id=total_amount align=right>&nbsp;</td>
<td align=right>{$total_cash|number_format:2}</td>
</tr>
</table>

<br>
<b>Close Counter</b>
<select name=clear_drawer>
<option value=0>No</option>
<option value=1 {if $item.clear_drawer or $smarty.request.clear_drawer}selected{/if}>Yes</option>
</select>
</div>

{* Currency *}
{if $foreign_currency_list}
	<div class="div_cd">
	<table class="tb" width="100%" cellpadding=4 cellspacing=0 border=0>
	<tr style="background:#fe9;">
	<th>Payment Type</th>
	<th>Amount</th>
	<th>Original/Default<br>Rate</th>
	<th>Rate</th>
	<th>Float</th>
	<th>Amount</th>
	{foreach from=$foreign_currency_list key=cc item=cc_code}
	<tr>
		<td><b>{$cc}</b></td>
		<td align=right><input style="text-align:right;" size=8 name=data[{$cc}] value="{$item.data.$cc|ifzero:0}"></td>
		<td align=right>{$item.ocurr_rate.$cc|ifzero:$pos_config.curr_rate.$cc}</td>
		<td align=right><input style="text-align:right;" size=8 name=curr_rate[{$cc}] value="{$item.curr_rate.$cc|ifzero:0}"></td>

		{assign var=curr_float_col_name value="`$cc`_Float"}
		<td><b>Float</b></td>
		<td align="right"><input style="text-align:right;" size="8" name="data[{$curr_float_col_name}]" value="{$item.data.$curr_float_col_name|ifzero:0}"></td>
	</tr>
	{/foreach}
	</table>
	</div>
{/if}
<input name=total_cash value="{$total_cash}" type=hidden>
<input name=total_amount value="{$total_amount}" type=hidden>
</form>
<br style="clear:both;">
<p align=center>
<input name=bsubmit type=button value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save('{$smarty.request.id}')">
{if $item.is_from_backend}<input type=button value="Delete" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_delete();">{/if}
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='counter_collection.php?a=view_by_date&date_select={$smarty.request.date}'">
</p>
<script>calc_subtotal();</script>
{include file=footer.tpl}
