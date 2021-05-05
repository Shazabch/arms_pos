{*
7/9/2018 2:53 PM Andy
- Enhanced base currency.
*}
<table width="100%" class="report_table">
	<tr class="header">
		<th>Date</th>
		<th>
			Rate <sup style="color:blue;">1</sup><br />
			(Foreign to {$config.arms_currency.code})<br />
			[<a href="javascript:void(alert('{$LANG.FOREIGN_CURRENCY_RATE_NOTICE|escape:javascript}'));">?</a>]
		</th>
		<th>
			Rate <sup style="color:blue;">2</sup><br />
			({$config.arms_currency.code} to Foreign)<br />
			[<a href="javascript:void(alert('{$LANG.FOREIGN_CURRENCY_BASE_RATE_NOTICE|escape:javascript}'));">?</a>]
		</th>
		<th>Updated By</th>
		<th>Timestamp</th>
	</tr>
	{foreach from=$currency_his_list item=r}
		<tr>
			<td align="center">{$r.date}</td>
			<td class="r rate_highlight">{$r.rate}</td>
			<td class="r base_rate_highlight">{$r.base_rate}</td>
			<td align="center">{$r.u}</td>
			<td align="center">{$r.timestamp}</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="5">-- No Data --</td>
		</tr>
	{/foreach}
</table>