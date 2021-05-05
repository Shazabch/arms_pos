<h1>Auto Redemption History</h1>

<div style="border:0px solid black;overflow-x:hidden;overflow-y:auto;height:350px;">
	<table class="report_table">
		<tr class="header">
			<th>Voucher Value</th>
			<th>Qty</th>
		</tr>
		{foreach from=$voucher_info.by_value key=voucher_value item=qty}
			<tr>
				<td><b>{$voucher_value}</b></td>
				<td class="r">{$qty|number_format}</td>
			</tr>
		{/foreach}
	</table>
</div>