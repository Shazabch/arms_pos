{*
4/7/2011 6:35:47 PM Alex
- add payment type summary

4/13/2011 4:26:01 PM Andy
- Change over amount variable to pre-calculate in php.

6/21/2011 6:17:38 PM Alex
- fix link to items details under summary details

9/6/2011 6:20:30 PM Alex
- add show multiple type of credit cards

10/14/2011 4:19:34 PM Andy
- Add payment type summary to skip "Discount" and "mix and match"

10/25/2012 11:25:00 AM Fithri
- bugfix - credit card total for individual type is accumulated from previous type, result in wrong total amount

1/17/2013 5:!1 PM Andy
- Add show how many quota used by the receipt in sales details.

2/21/2013 11:43 AM Andy
- Fix the quota used total only sum those active receipt.

7/4/2013 1:45 PM Andy
- Enhance to show cancel at backend in transaction list.

8/20/2013 10:15 AM Andy
- Add class negative for those negative payment amount.
- Add column "Deposit Used" and "Refund".

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

3/21/2014 4:14 PM Justin
- Enhanced to show custom payment type label if found it is set.

11/27/2014 5:03 PM Andy
- Enhance to show Service Charges, GST and Nett Sales 2.

03/24/2016 17:45 Edwin
- Enchanced on showing Receipt Reference Number in tables and details pop out

11/22/2016 2:37 PM Andy
- Add Special Cash Refund / Change.

10/1/2018 2:43 PM Andy
- Enhanced transaction list to able to show transaction actual amount.

10/3/2018 5:30 PM Andy
- Fixed total amount wrong if got show transaction actual amount.

10/14/2020 3:33 PM William
- Change GST word to Tax.
*}

{if $smarty.request.card_no and !$got_used_quota}
	{assign var=show_points value=1}
{/if}

<div style="overflow:auto;height:400px;">
<h1>Transaction Detail</h1>
<table class="tb" width=100% cellpadding=4 cellspacing=0 border=0>
<tr class=header style="background:#fe9">
<th>Transaction Time</th>
<th>Receipt No</th>
<th>Receipt Ref. No</th>
<th>Cashier</th>
<th>{if !$not_cc}Payment {/if}Amount</th>
{if $show_points}<th>Points</th>{/if}
{if $got_used_quota}<th>Quota Used</th>{/if}
{if $got_service_charge}<th>Service Charge</th>{/if}
{if $got_gst}<th>Tax</th>{/if}

{if !$not_cc}
<th>Over</th>
{/if}

{if $only_special_cash_change}<th>Cash Refund / Change</th>{/if}

{if $show_deposit_used_refund}
	<th>Deposit Used</th>
	<th>Refund</th>
{/if}

</tr>
{assign var=total_amount value=0}
{assign var=adjusted_total_amount value=0}
{assign var=total_point value=0}
{assign var=total_quota_used value=0}
{assign var=total_deposit_used_amt value=0}
{assign var=total_deposit_refund_amt value=0}
{assign var=total_service_charge value=0}
{assign var=total_gst_amt value=0}
{assign var=total_special_cash_change value=0}
{foreach from=$items item=item}
<tr>
<td>{$item.pos_time} {if $item.cancel_status}(Cancelled{if $item.cancel_at_backend} <sup class="sup_cancel_at_backend" title="Cancel at backend">@backend</sup>{/if}){/if}</td>
<td>
	<a href="javascript:void(0)" onclick="trans_detail('{$item.counter_id}','{$item.cashier_id}','{$item.date}','{$item.id}','{$item.branch_id}')">
		{receipt_no_prefix_format branch_id=$item.branch_id counter_id=$item.counter_id receipt_no=$item.receipt_no}
	</a>
</td>
<td>{$item.receipt_ref_no}</td>
<td>{$item.u}&nbsp;</td>

{assign var=row_amount value=$item.payment_amount}
{if $show_actual_amount}
	{assign var=row_amount value=$item.actual_amount}
{/if}
<td align="right" class="{if $row_amount<0}negative{/if}">
	{if $item.cc_type}({$item.cc_type}){/if}
	{$row_amount|number_format:2}
</td>


{if !$item.cancel_status}{assign var=total_amount value=$total_amount+$row_amount}{/if}
{if $show_points}
	<td align=right>{$item.point|number_format}</td>
	{assign var=total_point value=$total_point+$item.point}
{/if}

{if $got_used_quota}
	<td align="right">{$item.quota_used|number_format:2}</td>
	
	{if !$item.cancel_status}
		{assign var=total_quota_used value=$total_quota_used+$item.quota_used}
	{/if}
{/if}

{if $got_service_charge}
	{assign var=total_service_charge value=$total_service_charge+$item.real_service_charges}
	<td align="right">{$item.real_service_charges|number_format:2|ifzero:'-'}</td>
{/if}

{if $got_gst}
	{assign var=total_gst_amt value=$total_gst_amt+$item.real_gst_amt}
	<td align="right">{$item.real_gst_amt|number_format:2|ifzero:'-'}</td>
{/if}

{if !$not_cc}
<td align=right>{$item.over_amt|number_format:2|ifzero:'-'}</td>
{/if}

{if $only_special_cash_change}
	{assign var=total_special_cash_change value=$total_special_cash_change+$item.amount_change}
	<td align="right" class="{if $item.amount_change>0}negative{/if}">{$item.amount_change*-1|number_format:2|ifzero:'-'}</td>
{/if}

{if $show_deposit_used_refund}
	<td align="right">{$item.deposit_used_amt|number_format:2}</td>
	<td align="right" class="{if $item.deposit_refund_amt>0}negative{/if}">{$item.deposit_refund_amt*-1|number_format:2|ifzero:'-'}</td>
	
	{if !$item.cancel_status}
		{assign var=total_deposit_used_amt value=$total_deposit_used_amt+$item.deposit_used_amt}
		{assign var=total_deposit_refund_amt value=$total_deposit_refund_amt+$item.deposit_refund_amt}
	{/if}
{/if}
</tr>

{/foreach}
<tr style="background:#ffc">
<td colspan=4>Total</td>
<td align="right">{$total_amount|number_format:2}</td>
{if $show_points}
<td align=right>{$total_point|number_format}</td>
{/if}

{if $got_used_quota}
	<td align="right">{$total_quota_used|number_format:2}</td>
{/if}

{if $got_service_charge}
	<td align="right">{$total_service_charge|number_format:2}</td>
{/if}

{if $got_gst}
	<td align="right">{$total_gst_amt|number_format:2}</td>
{/if}

{if !$not_cc}<td align="right">-</td>{/if}

{if $only_special_cash_change}
	<td align="right" class="{if $total_special_cash_change>0}negative{/if}">{$total_special_cash_change*-1|number_format:2}</td>
{/if}

{if $show_deposit_used_refund}
	<td align="right">{$total_deposit_used_amt|number_format:2}</td>
	<td align="right" class="{if $total_deposit_refund_amt>0}negative{/if}">{$total_deposit_refund_amt*-1|number_format:2}</td>
{/if}
</tr>
</table>


<!------------ Check if got type and not equal to cash -------------->
{if $smarty.request.type && $smarty.request.type ne 'Cash' && $smarty.request.type ne 'Discount' and $smarty.request.type ne $mm_discount_col_value and $payment_type}
	<p>
	{assign var=pt value=$smarty.request.type}
	<h1>{$pos_config.payment_type_label.$pt|default:$pt} Summary</h1>
	{foreach from=$payment_type key=type item=other}
		{if $smarty.request.type eq 'Credit Cards'}<h3>{$pos_config.payment_type_label.$type|default:$type}</h3>{/if}
		{assign var=total_payment value=0}
		<table class="tb" width=100% cellpadding=4 cellspacing=0 border=0>
		<tr class=header style="background:#fe9">
			<th>{$pos_config.payment_type_label.$pt|default:$pt}</th>
			<th>Receipt No</th>
			<th>Amount</th>
		</tr>
		{foreach from=$other key=remark item=pp}
		<tr>
			<td valign="top" rowspan="{$pp.rowspan}">{$remark|default:'&nbsp;'}</td>
			{foreach name=start from=$receipt_no.$remark key=receipt item=re}
			    {if $smarty.foreach.start.first}
				<td>Receipt No: <a href="javascript:void(0)" onclick="trans_detail('{$re.counter_id}','{$re.cashier_id}','{$re.date}','{$re.pos_id}','{$re.branch_id}')">{receipt_no_prefix_format branch_id=$re.branch_id counter_id=$re.counter_id receipt_no=$receipt}</a> ({$re.payment_amount|number_format:2}) </td>
				{/if}
			{/foreach}
	
			<td valign="top" rowspan="{$pp.rowspan}" class="r">{$pp.payment_amount|number_format:2|ifzero}</td>
		 	{assign var=total_payment value=$total_payment+$pp.payment_amount}
		</tr>
			{foreach name=end from=$receipt_no.$remark key=receipt item=re}
			    {if !$smarty.foreach.end.first}
					<tr>
						<td>Receipt No: <a href="javascript:void(0)" onclick="trans_detail('{$re.counter_id}','{$re.cashier_id}','{$re.date}','{$re.pos_id}','{$re.branch_id}')">{receipt_no_prefix_format branch_id=$re.branch_id counter_id=$re.counter_id receipt_no=$receipt}</a> ({$re.payment_amount|number_format:2}) </td>
					</tr>
				{/if}
			{/foreach}
		{/foreach}
		<tr style="background:#ffc">
			<td>Total (Excluded Cancel Receipt)</td>
			<td>&nbsp;</td>
			<td class="r">{$total_payment|number_format:2|ifzero}</td>
		</tr>
		</table>
	{/foreach}
	</p>
{/if}

</div>
<form name=f_b>
<input type=hidden name=b value={$b}>
<input type=hidden name=sku value={$skc}>
</form>
