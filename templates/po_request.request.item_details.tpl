{*
8/7/2009 3:37:47 PM Andy
- Add to Show system stock and let user to key in stock balance as they want

5/4/2015 11:52 AM Andy
- Change to auto put stock balance.

10/2/2017 9:31 AM Justin
- Bug fixed the qty and ratio from sales trend table always rounded up instead of having decimal points.

06/24/2020 02:43 PM Sheila
- Fixed table boxes alignment and width.
*}

<b>Sales Trend</b>
<table class="input_no_border small body" cellspacing="1" cellpadding="1" style="border: 1px solid rgb(153, 153, 153); padding: 5px; background-color: rgb(255, 238, 153);">
	<tr bgcolor="#ffffff">
	    <th nowrap>
			<input class="tbl_col_salestrend" style="color:#000 !important;order:1px solid #ccc;background:#ccc; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="1M" disabled="">
			<input class="tbl_col_salestrend" style="color:#000 !important;border:1px solid #ccc;background:#ddd; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="3M" disabled="">
			<input class="tbl_col_salestrend" style="color:#000 !important;border:1px solid #ccc;background:#ccc; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="6M" disabled="">
			<input class="tbl_col_salestrend" style="color:#000 !important;border:1px solid #ccc;background:#ddd; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="12M" disabled="">	
		</th>
		<th>System Stock</th>
	</tr>
	<tr bgcolor="#ffffcc">
	    <td align=center nowrap>
		<div align=center>
			<input name="sales_trend[qty][1]" size=5 style="width:40px;background:#ccc;" value="{$item.sales_trend.qty.1|qty_nf:".":""|ifzero}" readonly>
			<input name="sales_trend[qty][3]" style="width:40px; background:#ddd;" size=5 value="{$item.sales_trend.qty.3|qty_nf:".":""|ifzero}" readonly>
			<input name="sales_trend[qty][6]" size=5 style="width:40px;background:#ccc;" value="{$item.sales_trend.qty.6|qty_nf:".":""|ifzero}" readonly>
			<input name="sales_trend[qty][12]" style="width:40px; background:#ddd;" size=5 value="{$item.sales_trend.qty.12|qty_nf:".":""|ifzero}" readonly>
		</div>
		<div align=center style="padding-top:2px">
			<input size=5 style="width:40px;background:#ccc;" value="{$item.sales_trend.qty.1|qty_nf:".":""|ifzero}" readonly>
			<input style="width:40px; background:#ddd;" size=5 value="{$item.sales_trend.qty.3/3|qty_nf:".":""|ifzero}" readonly>
			<input size=5 style="width:40px;background:#ccc;" value="{$item.sales_trend.qty.6/6|qty_nf:".":""|ifzero}" readonly>
			<input style="width:40px; background:#ddd;" size=5 value="{$item.sales_trend.qty.12/12|qty_nf:".":""|ifzero}" readonly>
		</div>
		</td>
		<td align="center">
			{$item.available_stock|number_format}
			<input type="hidden" name="system_stock" value="{$item.available_stock}" />
		</td>
	</tr>
</table>

<script>
var available_stock = '{$item.available_stock}';
{literal}
document.f_a['balance'].value = available_stock;
//document.f_a['balance'].readOnly = true;
//document.f_a['balance'].value = '';
{/literal}
</script>
