{*
05/05/2016 09:30 Edwin
- Bug fixed at missing values at receipt remark.

05/05/2016 11:00 Edwin
- Enhanced on show logo in tax invoice when config.full_tax_invoice_show_logo is enabled.

5/10/2016 2:12 PM Andy
- Fix to load site config.

1/3/2017 17:00 Qiu Ying
- Bug fixed on print full tax invoice the invoice number does not match with the receipt number

2/16/2017 14:38 Qiu Ying
- Bug fixed on security deposit receive document title is wrong

11/16/2017 9:38 AM Kee Kee
- Added GST Relief Clause remark

10/12/2020 5:38 PM William
- Change GST word to Tax.
*}
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	{config_load file="site.conf"}
	<title>Tax Invoice - {$receipt_ref_no}</title>
	<link rel="stylesheet" type="text/css" href="/templates/pos.print_full_tax_invoice.css">
</head>
<body onload="window.print()">
<table class="no_line" border="0" width="100%">
	<tr>
		<td align="center">
			{if $config.full_tax_invoice_show_logo}
				<img src="{get_logo_url mod='full_tax_invoice'}" height="100" style="max-width:100px;">
			{else}
				&nbsp;
			{/if}
		</td>
		<td width="100%">
			<table class="no_line" border=0>
			{foreach from=$receipt_header item=rh}
			<tr>
				<td>{$rh}</td>
			</tr>
			{/foreach}
			</table>
		</td>
		<td valign="top" align="right">
			<table class="no_line" border=0>
			<tr>
				<td>Invoice No</td><td>:</td><td>{$receipt_ref_no}</td>
			</tr>
			<tr>
				<td>Date</td><td>:</td><td>{$date}</td>
			</tr>
			</table>
		</td>
	</tr>
</table>
<br style="clear:both;"/>
<center><b>{if !$is_security_deposit_type}TAX INVOICE{/if}</b></center>
<br style="clear:both;"/>
{if $is_duplicate_copy}<center><b>{$is_duplicate_copy}</b></center>{/if}
{if $is_deposit}<center><b>Deposit Receive</b></center>{/if}
<br style="clear:both;"/>
<div style="float:left;width:70%;">
	<table class="no_line" border=0;>
		{foreach from=$customer_info key=title item=ci}
		<tr>
			<td>{$title}</td>
			<td>:</td>
			<td style="width:90%">{$ci}</td>
		</tr>
		{/foreach}
	</table>
</div>
<br style="clear:both;"/>
{assign var=total_ecl_gst value=0}
{assign var=total_gst value=0}
{assign var=amount value=0}
{if $is_deposit}
	{if $items}
		<table style="margin-top:1em;" width="100%">	
			<thead>
			<tr class="invoice_total">
				<td style="text-align: left !important;">No</td>
				<td class="desc_space" style="text-align: left !important;">Description</td>
				<td>Qty</td>
				<td align="center"><b>Unit Price ({$default_currency})</b></td>
				<td align="center"><b>Total ({$default_currency})</b></td>
			</tr>	
			</thead>
			{foreach from=$items item=item}
			<tr class="invoice_items">
				<td>{$item.no}</td>
				<td class="desc_space">{$item.receipt_description}</td>
				<td align="right">{$item.qty}</td>
				<td align="right">{if $item.remark}{$item.remark}{/if}{$item.unit_price|number_format:2}</td>
				<td align="right">{$item.amount|number_format:2}</td>
			</tr>	
			{assign var=amount value=$item.amount+$amount}
			{/foreach}		
			<tr class="invoice_total">
				<td colspan="4">
					<b>TOTAL AMOUNT DUE</b>
				</td>
				<td>{$amount|number_format:2}</td>
			</tr>
			{foreach from=$payment item=pay key=pkey name="payment"}
			<tr class="trans_summary">
				{if $smarty.foreach.payment.first}
				<td rowspan="{$payment|@count}" colspan="3" valign="top">
					<div class="gst_summary">
						<table border=0 cellspacing=0>
							<tr>
								<th>Tax</th>
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
				</td>
				{/if}
				<td align="center"><b>{$pay.0}</b></td>
				<td valign="top">{$pay.1}</td>
			</tr>
			{/foreach}
		</table>
	{else}
		<br />
		{foreach from=$payment item=pay key=pkey name="payment"}
			<b>{$pay.0}{$pay.1}</b>
		{/foreach}
		<br />
	{/if}
{else}
<table style="margin-top:1em;" width="100%">	
	<thead>
	<tr class="invoice_total">
		<td>No</td>
		<td class="desc_space" style="text-align: left !important;">Description</td>
		<td>Qty</td>
		<td align="center"><b>Unit Price<br/>({$default_currency})</b></td>
		<td align="center"><b>Sub Total<br/>({$default_currency})</b></td>
		<td align="center"><b>Disc<br/>({$default_currency})</b></td>
		<td align="center"><b>Total Excl.<br/>Tax({$default_currency})</b></td>
		<td align="center"><b>Tax<br/>({$default_currency})</b></td>
		<td align="center"><b>Total Incl.<br/>Tax({$default_currency})</b></td>
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
		<td align="right">{$item.total_ecl_gst|number_format:2}</td>
		<td align="right">{$item.total_gst|number_format:2}</td>
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
		<td>{$total_ecl_gst|number_format:2}</td>
		<td>{$total_gst|number_format:2}</td>
		<td>{$amount|number_format:2}</td>
		<td class="no_border_col"></td>
	</tr>
	{if $service_charges_info}
	<tr class="invoice_total">
		<td colspan="6"><b>Service Charge ({$service_charges_info.rate}%)</b></td>
		<td>{$service_charges_info.amount|number_format:2}</td>
		<td>{$service_charges_info.gst_amount|number_format:2}</td>
		<td>{$service_charges_info.total|number_format:2}</td>
		<td class="no_border_col" style="text-align: left !important;">{$service_charges_info.sc_gst_detail.indicator_receipt}</td>
	</tr>
	{/if}
	{foreach from=$payment item=pay key=pkey name="payment"}
	<tr class="trans_summary">
		{if $smarty.foreach.payment.first}
		<td rowspan="{$payment|@count}" colspan="6" valign="top">
			<div class="gst_summary">
				<table border=0 cellspacing=0>
					<tr>
						<th>Tax</th>
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
				{if $se_clause_remark}
				<div style="text-align:left; margin-top:1em;">
					<b>Tax Relief Clause:</b><br />
					{$se_clause_remark}
				</div>
				<br/>
				{/if}
			</div>
		</td>
		{/if}
		<td colspan="2" align="center"><b>{$pay.0}</b></td>
		<td>{$pay.1}</td>
	</tr>
	{/foreach}
</table>
{/if}
<br style="clear:both;"/>
{if $receipt_remark}
<b>Receipt Remark</b><br />
<table class="no_border" border=0 style="margin-top:1em;" width="100%">
	{foreach from=$receipt_remark item=rr}
	<tr>
		<td><b>{$rr.title}</b></td>
		<td>:</td>
		<td style="width:90%;">{$rr.value}</td>
	</tr>
	{/foreach}
</table>
{/if}
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
				<td><b>Invoice No<b></td><td>:</td><td colspan="2">{$receipt_no}</td>
			</tr>
			<tr>
				<td><b>Date<b></td><td>:</td><td colspan="2">{$date}</td>
			</tr>
			<tr>
				<td><b>Credit Note No</b></td><td>:</td><td colspan="2">{$cn.credit_note_ref_no}</td>
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
				<td><b>Tax Register Number</b></td><td>:</td><td colspan="2">{$cn.gst_register_number}</td>
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
{if $is_deposit and $items}
<p><b>"*" is follow latest price</b></p>
{/if}
{*
{padline symbol="_" column=30 assign="sign_line"}
<table border=0 style="margin-top:6em;" width="100%">
	<tr>
		<td>{$sign_line}</td>
	</tr>
	<tr>
		<td><b>{$company_name}</b></td>
	</tr>
</table>

<table border=0 style="margin-top:3em;" width="100%">
	<tr>
		<td colspan=3>************</td>
	</tr>
	<tr>
		<td>S = Standard Rated(6%)</td>
	</tr>
	<tr>
		<td>Z = Zero Rated (6%)</td>
	</tr>	
	<tr>
		<td>E = Exempt</td>
	</tr>
</table>*}
</body></html>
