{*
3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.
*}
<tr style="background:#addfff !important" class="member_child_{$card_no}">
	<th>&nbsp;</th>
	<th>Branch</th>
	<th>POS Time</th>
	<th>Receipt No</th>
	<th>Cashier Name</th>
	<th>Amount</th>
	<th>Points</th>
</tr>
{foreach from=$table key=key item=r name=mm}
	<tr style="background:#e0ffff !important" class="member_child_{$card_no}">
		<td align="center">&nbsp;</td>
		<td align="center">{$r.branch_code}</td>
		<td align="center">{$r.pos_time}</td>
		<td align="center"><a class="clickable" href="javascript:items_details('{$r.branch_id}','{$r.counter_id}','{$r.id}','{$r.date}')">{receipt_no_prefix_format branch_id=$r.branch_id counter_id=$r.counter_id receipt_no=$r.receipt_no}</a></td>
		<td align="center">{$r.cashier_name}</td>
		<td align="right">{$r.amount|number_format:2}</td>
		<td align="right">{$r.point|number_format:0}</td>
	</tr>
	{assign var=ttl_amount value=$ttl_amount+$r.amount}
	{assign var=ttl_points value=$ttl_points+$r.point}
{/foreach}
<tr style="background:#addfff !important" class="member_child_{$card_no}">
		<td align="right" colspan="5"><b>Sub Total</b></td>
		<td align="right">{$ttl_amount|number_format:2}</td>
		<td align="right">{$ttl_points|number_format:0}</td>
</tr>
