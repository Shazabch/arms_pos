{*
11/24/2016 3:48 PM Andy
- Enhanced to show item details by receipt.
- Enhanced to able to export item details.

11/24/2016 5:40 PM Andy
- Removed column Qty.

3/1/2017 11:56 AM Justin
- Enhanced to trigger deposit data.
*}

{include file='header.tpl'}

<h1>{$PAGE_TITLE} - by Items</h1>

{if !$no_header_footer}
<form name="f_a">
	<input type="hidden" name="a" value="show_items" />
	<input type="hidden" name="branch_id" value="{$smarty.request.branch_id}" />
	<input type="hidden" name="date" value="{$smarty.request.date}" />
	<input type="hidden" name="gst_indicator" value="{$smarty.request.gst_indicator}" />
	<input type="hidden" name="show_by_tax_code" value="{$smarty.request.show_by_tax_code}" />
	
	{if $sessioninfo.privilege.EXPORT_EXCEL}
		<button name="output_excel"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	{/if}
</form>
{/if}
<h2>{$report_title}</h2>

{if !$data && !$deposit_data}
	* No Data
{else}
	{if $data}
		<h2>POS</h2>
		<table width="100%" class="report_table">
			<tr class="header">
				<th>No.</th>
				<th>Receipt Ref Number</th>
				<th>ARMS CODE</th>
				<th>MCode</th>
				<th>Art No.</th>
				<th>Description</th>
				{*<th>Qty</th>*}
				<th>Amt</th>
				<th>GST</th>
				<th>Amt Inc. GST</th>
			</tr>
			{assign var=row_number value=1}
			{foreach from=$data.data key=receipt_ref_no item=pi_list}
				{foreach from=$pi_list item=pi}
					{assign var=sid value=$pi.sid}
					<tr>
						<td>{$row_number}.</td>
						<td>{$receipt_ref_no}</td>
						<td>{$data.si_info.$sid.sku_item_code}</td>
						<td>{$data.si_info.$sid.mcode}</td>
						<td>{$data.si_info.$sid.artno}</td>
						<td>{$data.si_info.$sid.description}</td>
						{*<td align="right">{$pi.qty|qty_nf}</td>*}
						<td align="right">{$pi.before_tax_price|number_format:2}</td>
						<td align="right">{$pi.tax_amount|number_format:2}</td>
						<td align="right">{$pi.amt_included_gst|number_format:2}</td>
					</tr>
					{assign var=row_number value=$row_number+1}
				{/foreach}
			{/foreach}
			<tr class="header">
				<th align="right" colspan="6">Total</th>
				{*<th align="right">{$data.total.qty|qty_nf}</th>*}
				<th align="right">{$data.total.before_tax_price|number_format:2}</th>
				<th align="right">{$data.total.tax_amount|number_format:2}</th>
				<th align="right">{$data.total.amt_included_gst|number_format:2}</th>
			</tr>
		</table>
	{/if}
	
	{if $deposit_data}
		<br />
		<h2>Deposit</h2>
		<table width="100%" class="report_table">
			<tr class="header">
				<th>No.</th>
				<th>Receipt Ref Number</th>
				<th>Deposit Status</th>
				<th width="10%">Amt</th>
				<th width="10%">GST</th>
				<th width="10%">Amt Inc. GST</th>
			</tr>
			{assign var=row_number value=1}
			{foreach from=$deposit_data.data key=receipt_ref_no item=di}
				<tr>
					<td>{$row_number}.</td>
					<td>{$receipt_ref_no}</td>
					<td align="center">{$di.status}</td>
					<td align="right">{$di.before_tax_price|number_format:2}</td>
					<td align="right">{$di.tax_amount|number_format:2}</td>
					<td align="right">{$di.amt_included_gst|number_format:2}</td>
				</tr>
				{assign var=row_number value=$row_number+1}
			{/foreach}
			<tr class="header">
				<th align="right" colspan="3">Total</th>
				<th align="right">{$deposit_data.total.before_tax_price|number_format:2}</th>
				<th align="right">{$deposit_data.total.tax_amount|number_format:2}</th>
				<th align="right">{$deposit_data.total.amt_included_gst|number_format:2}</th>
			</tr>
		</table>
	{/if}
	
	{if $data && $deposit_data}
		<h2>Summary</h2>
		<table width="50%" class="report_table">
			<tr class="header">
				<th>Type</th>
				<th width="10%">Amt</th>
				<th width="10%">GST</th>
				<th width="10%">Amt Inc. GST</th>
			</tr>
			<tr>
				<td>POS</td>
				<td align="right">{$data.total.before_tax_price|number_format:2}</td>
				<td align="right">{$data.total.tax_amount|number_format:2}</td>
				<td align="right">{$data.total.amt_included_gst|number_format:2}</td>
			</tr>
			<tr>
				<td>Deposit</td>
				<td align="right">{$deposit_data.total.before_tax_price|number_format:2}</td>
				<td align="right">{$deposit_data.total.tax_amount|number_format:2}</td>
				<td align="right">{$deposit_data.total.amt_included_gst|number_format:2}</td>
			</tr>
			<tr class="header">
				<th align="right">Grand Total</th>
				<th align="right">{$data.total.before_tax_price+$deposit_data.total.before_tax_price|number_format:2}</th>
				<th align="right">{$data.total.tax_amount+$deposit_data.total.tax_amount|number_format:2}</th>
				<th align="right">{$data.total.amt_included_gst+$deposit_data.total.amt_included_gst|number_format:2}</th>
			</tr>
		</table>
	{/if}
{/if}

{include file='footer.tpl'}