{*
3/11/2015 5:37 PM Andy
- Enhanced to show (FOC) when item foc is tick or price is zero.
*}

<table cellpadding="2" cellspacing="1" border="0" width="100%">
	<tr height="24" bgcolor="#ffee99">
		<th>Date</th>
		<th>Price</th>
		<th>Price Type</th>
		<th>User</th>
	</tr>
	{foreach from=$rp_history item=r}
	<tr>
		<td>{$r.date}</td>
		<td class="r">{$r.price|number_format:2}
			{if $r.price eq '0' or $r.selling_price_foc}
				<br />
				<i>(FOC)</i>
			{/if}
		</td>
		<td align="center">{$r.trade_discount_code}</td>
		<td align="center">{$r.username|default:"<font color=green>System</font>"}</td>
	</tr>
	{/foreach}
</table>