{*
9/4/2018 5:09 PM Andy
- Enhanced "Print Full Tax Invoice" to able to print non-gst transaction.

6/19/2019 5:44 PM William
- Added new Vertical print format.
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
		{if $system_settings.logo_vertical eq 1}
		<td colspan="2"  width="100%">
			<table width="100%" style="text-align: center;">
				<tr>
				{if $config.full_tax_invoice_show_logo}
					<td><img src="{get_logo_url mod='full_tax_invoice'}" height="100" style="max-width:600px;max-height: 80px;"></td>
				{else}
					<td>&nbsp;</td>
				{/if}
				</tr>
			</table>
			<table class="no_line" border=0  width="100%" style="text-align: center;">
			{foreach from=$receipt_header item=rh}
			<tr>
				<td>{$rh}</td>
			</tr>
			{/foreach}
			</table>
		</td>
		{else}
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
		{/if}
		<td valign="top" align="right"  {if $system_settings.logo_vertical eq 1} style="transform: translateY(50%)"; {/if}>
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
<center><b>{if !$is_security_deposit_type}INVOICE{/if}</b></center>
<br style="clear:both;"/>
{if $is_duplicate_copy}<center><b>{$is_duplicate_copy}</b></center>{/if}
{if $is_deposit}<center><b>Deposit Receive</b></center>{/if}
<br style="clear:both;"/>
<div style="float:left;width:70%;">
	<table class="no_line" border=0;>
		{foreach from=$customer_info key=title item=ci}
			{if $ci}
				<tr>
					<td>{$title}</td>
					<td>:</td>
					<td style="width:90%">{$ci}</td>
				</tr>
			{/if}
		{/foreach}
	</table>
</div>
<br style="clear:both;"/>
{assign var=amount value=0}
{if $is_deposit}
	{if $items}
		<table style="margin-top:1em;" width="100%">	
			<thead>
			<tr class="invoice_total">
				<td style="text-align: left !important;">No</td>
				<td style="text-align: left !important;">Barcode</td>
				<td class="desc_space" style="text-align: left !important;">Description</td>
				<td>Qty</td>
				<td align="center"><b>Unit Price ({$default_currency})</b></td>
				<td align="center"><b>Total ({$default_currency})</b></td>
			</tr>	
			</thead>
			{foreach from=$items item=item}
			<tr class="invoice_items">
				<td>{$item.no}</td>
				<td>{$item.barcode}</td>
				<td class="desc_space">{$item.receipt_description}</td>
				<td align="right">{$item.qty}</td>
				<td align="right">{if $item.remark}{$item.remark}{/if}{$item.unit_price|number_format:2}</td>
				<td align="right">{$item.amount|number_format:2}</td>
			</tr>	
			{assign var=amount value=$item.amount+$amount}
			{/foreach}		
			<tr class="invoice_total">
				<td colspan="5">
					<b>TOTAL AMOUNT DUE</b>
				</td>
				<td>{$amount|number_format:2}</td>
			</tr>
			{foreach from=$payment item=pay key=pkey name="payment"}
			<tr class="trans_summary">
				<td colspan="4" valign="top"></td>
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
		<td style="text-align: left !important;">No</td>
		<td style="text-align: left !important;">Barcode</td>
		<td class="desc_space" style="text-align: left !important;">Description</td>
		<td>Qty</td>
		<td align="center"><b>Unit Price<br/>({$default_currency})</b></td>
		<td align="center"><b>Sub Total<br/>({$default_currency})</b></td>
		<td align="center"><b>Disc<br/>({$default_currency})</b></td>
		
		<td align="center"><b>Total ({$default_currency})</b></td>
	</tr>	
	</thead>
	{foreach from=$items item=item}
	<tr class="invoice_items">
		<td>{$item.no}</td>
		<td>{$item.barcode}</td>
		<td class="desc_space">{$item.receipt_description}{if $item.return_receipt_no}<br />Receipt No: {$item.return_receipt_no}<br />Date: {$item.return_date}{/if}</td>
		<td align="right">{$item.qty}</td>
		<td align="right">{$item.unit_price|number_format:2}</td>
		<td align="right">{$item.sub_total|number_format:2}</td>
		<td align="right">{$item.disc|number_format:2}</td>
		<td align="right">{$item.amount|number_format:2}</td>
		{assign var=amount value=$item.amount+$amount}
	</tr>	
	{/foreach}		
	<tr class="invoice_total">
		<td colspan="7">
			<b>TOTAL AMOUNT DUE</b>
		</td>
		<td>{$amount|number_format:2}</td>
	</tr>
	{if $service_charges_info}
	<tr class="invoice_total">
		<td colspan="7"><b>Service Charge ({$service_charges_info.rate}%)</b></td>
		<td>{$service_charges_info.total|number_format:2}</td>
	</tr>
	{/if}
	{foreach from=$payment item=pay key=pkey name="payment"}
	<tr class="trans_summary">
		<td colspan="5" valign="top">
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

{if $is_deposit and $items}
<p><b>"*" is follow latest price</b></p>
{/if}
</body></html>
