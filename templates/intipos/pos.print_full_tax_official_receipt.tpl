{*
5/10/2016 2:12 PM Andy
- Fix to load site config.

10/1/2018 11:12 AM Andy
- Enhanced "Print Official Receipt" to able to print non-gst transaction.
*}

<html>
{literal}
<style>
	table.tnc tr td, table.rh tr td{
		padding:0px; margin:0px;
	}
</style>
{/literal}
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	{config_load file="site.conf"}
	<title>Tax Invoice - {$invoice_no}</title>
	<link rel="stylesheet" type="text/css" href="/templates/pos.print_full_tax_invoice.css">
</head>
<body onload="window.print()">
<table class="no_line" border="0" width="100%">
	<tr>
		<td><img src="templates/intipos/intipos_long.png" height="60"></td>
		<td valign="top" align="right">
			<table class="no_line" border=0>
			<tr>
				<td>Receipt No</td><td>:</td><td>{$invoice_no}</td>
			</tr>
			<tr>
				<td>Date</td><td>:</td><td>{$date}</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="100%">
			<table class="rh" border=0>
			{foreach from=$receipt_header item=rh}
				<tr><td>{$rh}</td></tr>
			{/foreach}
			</table>
		</td>
	</tr>		
</table>
<br style="clear:both;"/>
<center><b style="font-size: x-large">OFFICIAL RECEIPT</b></center>
<br style="clear:both;"/>
<div style="float:left;width:70%;">
	<table class="member_info" border=0;>
		{foreach from=$customer_info key=title item=ci name=i}
			<tr>
				{if $smarty.foreach.i.index == 1}
				<td>ID</td><td>:</td>
				<td style="width:90%">{$member_no}</td>
				</tr>
				<tr>
					<td>{$title}</td>
					<td>:</td>
					<td style="width:90%">{$ci}</td>
				{else}
					<td>{$title}</td>
					<td>:</td>
					<td style="width:90%">{$ci}</td>
				{/if}
			</tr>
		{/foreach}
		{if $receipt_remark}
		<tr>
			<td valign="top">Receipt Remark</td><td valign="top">:</td>
			<td>
				{foreach from=$receipt_remark item=rr}
					{$rr.value}</br>
				{/foreach}
			</td>
		</tr>
		{/if}
	</table>
</div>
<br style="clear:both;"/>
{assign var=total_ecl_gst value=0}
{assign var=total_gst value=0}
{assign var=amount value=0}
<table style="margin-top:1em;" width="100%">	
	<thead>
	<tr class="invoice_total">
		<td>No</td>
		<td class="desc_space" style="text-align: left !important;">Description</td>
		<td>Qty</td>
		<td align="center"><b>Unit Price<br/>({$default_currency})</b></td>
		<td align="center"><b>Sub Total<br/>({$default_currency})</b></td>
		<td align="center"><b>Disc<br/>({$default_currency})</b></td>
		{if $pos.is_gst}
			<td align="center"><b>Total Excl.<br/>GST({$default_currency})</b></td>
			<td align="center"><b>GST<br/>({$default_currency})</b></td>
		{/if}
		<td align="center"><b>Total{if $pos.is_gst} Incl.<br/>GST{else}<br />{/if}({$default_currency})</b></td>
		<td style="border:none !important;"></td>
	</tr>	
	</thead>
	{foreach from=$items item=item}
	<tr class="invoice_items">
		<td>{$item.no}</td>
		<td class="desc_space">{$item.receipt_description}{if $item.return_receipt_no}<br />Receipt No: {$item.return_receipt_no}<br />Date: {$item.return_date}{/if}</td>
		<td align="right">{$item.qty}</td>
		<td align="right">{$item.unit_price|number_format:2}</td>
		<td align="right">{$item.sub_total|number_format:2}</td>
		<td align="right">{$item.disc|number_format:2}</td>
		{if $pos.is_gst}
			<td align="right">{$item.total_ecl_gst|number_format:2}</td>
			<td align="right">{$item.total_gst|number_format:2}</td>
		{/if}
		<td align="right">{$item.amount|number_format:2}</td>
		<td class="no_border_col">{$item.indicator}</td>
		{assign var=total_ecl_gst value=$item.total_ecl_gst+$total_ecl_gst}
		{assign var=total_gst value=$item.total_gst+$total_gst}
		{assign var=amount value=$item.amount+$amount}
	</tr>	
	{/foreach}		
	<tr class="invoice_total">
		<td colspan="6">
			<b>TOTAL AMOUNT DUE</b>
		</td>
		{if $pos.is_gst}
			<td>{$total_ecl_gst|number_format:2}</td>
			<td>{$total_gst|number_format:2}</td>
		{/if}
		<td>{$amount|number_format:2}</td>
		<td class="no_border_col"></td>
	</tr>
	{if $service_charges_info}
	<tr class="invoice_total">
		<td colspan="6"><b>Service Charge ({$service_charges_info.rate}%)</b></td>
		{if $pos.is_gst}
			<td>{$service_charges_info.amount|number_format:2}</td>
			<td>{$service_charges_info.gst_amount|number_format:2}</td>
		{/if}
		<td>{$service_charges_info.total|number_format:2}</td>
		<td class="no_border_col" style="text-align: left !important;">{$service_charges_info.sc_gst_detail.indicator_receipt}</td>
	</tr>
	{/if}
	{assign var=cols value=4}
	{if $pos.is_gst}{assign var=cols value=$cols+2}{/if}
	{foreach from=$payment item=pay key=pkey name="payment"}
	<tr class="trans_summary">
		{if $smarty.foreach.payment.first}
			<td rowspan="{$payment|@count}" colspan="{$cols}" valign="top">
				{if $pos.is_gst}
					<div class="gst_summary">
						<table border=0 cellspacing=0>
							<tr>
								<th>GST</th>
								<th>Amount</th>
								<th>Tax</th>
							</tr>
							{foreach from=$gst_arr key=k item=g}
								<tr>
									<td>{$k}</td>
									<td>{$g.before_tax_price}</td>
									<td>{$g.total_gst}</td>
								</tr>
							{/foreach}
						</table>
					</div>
				{/if}
			</td>
		{/if}
		<td colspan="2" align="center"><b>{$pay.0}</b></td>
		<td>{$pay.1}</td>
	</tr>
	{/foreach}
</table>
<br style="clear:both;"/>

{if $special_exemption}
<b>Special Exemption Remark</b><br />
<table class="no_border" border=0 style="margin-top:1em;" width="100%">
	{foreach from=$special_exemption.remark key=title item=remark}
	<tr>
		<td><b>{$title}</b></td>
		<td>:</td>
		<td style="width:90%;">{$remark}</td>
	</tr>
	{/foreach}
</table>
{/if}
{if $credit_note_info}
<br />
<br />
<b>Credit Note</b><br />
{foreach from=$credit_note_info key=date item=dates}
	{foreach from=$dates key=receipt_no item=cn}
		<table class="no_border" border=0 style="margin-top:1em;">
			<tr>
				<td><b>Receipt No<b></td><td>:</td><td colspan="2">{$receipt_no}</td>
			</tr>
			<tr>
				<td><b>Date<b></td><td>:</td><td colspan="2">{$date}</td>
			</tr>
			<tr>
				<td><b>Credit Note No</b></td><td>:</td><td colspan="2">{$cn.credit_note_no}</td>
			</tr>
			<tr>
				<td><b>Credit Note Date</b></td><td>:</td><td colspan="2">{$cn.return_date}</td>
			</tr>
			<tr>
				<td ><b>Company Name</b></td><td>:</td><td colspan="2">{$cn.company_name}</td>
			</tr>
			<tr>
				<td valign="top"><b>Address</b></td>
				<td valign="top">:</td>
				<td colspan="2">{$cn.address|nl2br}</td>
			</tr>
			{if $config.register_gst}
			<tr>
				<td><b>GST Register Number</b></td><td>:</td><td colspan="2">{$cn.gst_register_number}</td>
			</tr>
			{/if}
			{foreach from=$cn.customer_info key=t item=v}
			<tr>
				<td><b>{$t}</b></td><td>:</td><td colspan="2">{$v}</td>
			</tr>
			{/foreach}
			<tr><td></td></tr>
			{foreach from=$cn.item_infor item=cn_item key=k}
				{if $k!='deposit'}
				<tr>
					<td colspan="4">{$k}</td>
				</tr>
				{/if}
				<tr>
					<td><b>Reason</b></td>
					<td>:</td>
					<td>{$cn_item.return_reason}</td>
					<td align="right">{$default_currency} {$cn_item.return_amount|number_format:2}</td>
				</tr>
			{/foreach}
			<tr>
				<td><b>Total</b></td>
				<td>:</td>
				<td></td>
				<td align="right">{$default_currency} {$cn.amount|number_format:2}</td>
			</tr>
		</table>
		<br/>
	{/foreach}
{/foreach}
{/if}
<table class="tnc">
	<tr><td>Note:</td></tr>
	<tr><td>1. All fees and rental paid are not refundable and non-transferable.</td></tr>
	<tr><td>2. Please retain this official receipt for future reference and deposit refund.</td></tr>
	<tr><td>3. For cheque payment, this official receipt is valid upon clearance of the cheque.</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>This is a system generated document. No signature is required.</td></tr>
</table>
</body></html>
