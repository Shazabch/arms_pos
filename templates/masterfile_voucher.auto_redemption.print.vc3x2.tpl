{*
4/21/2017 9:52 AM Khausalya 
- Enhanced changes from RM to use config setting. 

5/8/2017 9:36 AM Khausalya
- Enhanced changes from Ringgit Malaysia to use config setting
*}
{if !$skip_header}
{include file='header.print.tpl'}

<body onload="window.print()">
{/if}


{literal}
<style>
.barcode {
	font-family: "MRV Code39extMA", verdana, calibri;
	font-size: 7pt;
}

.amount_data{
	width:45mm;top:13mm;
}

.amount
{
	font-size:30pt;
	text-align:center;
	font-weight:bold;
	width:38mm;
}

.amount_str
{
	font-size:8pt;
	margin-top: -0.1in;
	text-align:center;
	width:38mm;
}

.date_setting{
	width:45mm;top:30mm;
	font-size:8pt;
}
</style>
{/literal}

{foreach from=$page_list key=page item=page_data}
	{assign var=nric value=$page_data.nric}
	
	<div class="printarea">
		<div style="border:0px solid black;height:630px;">
			<table border="1" width="100%">
				{foreach from=$page_data.page_voucher key=row item=vc_list}
					<tr height="200">
						{foreach from=$vc_list key=col item=voucher}
							<td width="50%" align="center">
								<b>Cash Voucher</b>
								<div class="barcode">*{$voucher.barcode_voucher_prefix}{$voucher.secur_barcode}*</div>
								
								<div class="amount_data" style="left:0mm">
									<div class="amount">{$config.arms_currency.symbol} {$voucher.voucher_value}</div>
									<div class="amount_str">{$config.arms_currency.name} {convert_number number=$voucher.voucher_value show_decimal=1} only</div>
								</div>
								
								<div class="date_setting">
									Valid From: {$voucher.valid_from|date_format:"%Y-%m-%d"}<br />
									Valid Until: {$voucher.valid_to|date_format:"%Y-%m-%d"}
								</div>
							</td>				
						{/foreach}
					</tr>
				{/foreach}
			</table>
		</div>
		<div>
			Member Name: {$mem_info.$nric.name}<br />
			Card No: {$mem_info.$nric.card_no}<br />
			Address:
			{$mem_info.$nric.address|nl2br}<br />
			Post Code: {$mem_info.$nric.postcode}<br />
			City: {$mem_info.$nric.city}<br />
			State: {$mem_info.$nric.state}<br />
		</div>
	</div>
{/foreach}

{section name=page loop=$pages.sheet}
<div class=printarea>
	<div class="margin_offset sample" >
		<table class='table_height' style="border:0px solid black;border-collapse:collapse">
			{section name=row loop=$pages.row}
			    <tr style="border:0px solid black;border-collapse:collapse">
					<td valign="top">
						<div style="position:relative">
				            {assign var=col value=0}
				            <div class="title_barcode" style="left:51mm">
				            {if $voucher[page][row][$col].secur_barcode}
								<div class="voucher_code">
									<div class="voucher_title"><b>Cash Voucher</b></div>
									<div class=barcode>*{$voucher[page][row][$col].barcode_voucher_prefix}{$voucher[page][row][$col].secur_barcode}*</div>
								</div>
								<div class="amount_data" style="left:0mm">
									<div class=amount>{$config.arms_currency.symbol} {$voucher[page][row][$col].voucher_value}</div>
									<div class=amount_str>{$config.arms_currency.name} {convert_number number=$voucher[page][row][$col].voucher_value show_decimal=1} only</div>
								</div>
								<div class="date_setting">
									Valid From: <br />
									Valid Until:
								</div>
							{else}
							    &nbsp;
							{/if}	
							</div>
							
							{assign var=col value=1}
				            <div class="title_barcode" style="left:146mm;">
				            {if $voucher[page][row][$col].secur_barcode}
								<div class="voucher_code">
									<div class="voucher_title" style="top:10mm;left:10mm;"><b>Cash Voucher</b></div>
									<div class="barcode">*{$voucher[page][row][$col].barcode_voucher_prefix}{$voucher[page][row][$col].secur_barcode}*</div>
								</div>
								<div class="amount_data" style="left:0mm;">
									<div class=amount>{$config.arms_currency.symbol} {$voucher[page][row][$col].voucher_value}</div>
									<div class=amount_str>{$config.arms_currency.name} {convert_number number=$voucher[page][row][$col].voucher_value show_decimal=1} only</div>
								</div>
								<div class="date_setting">
									Valid From:<br />
									Valid Until:
								</div>
							{else}
							    &nbsp;
							{/if}	
							</div>
						</div>
					</td>
				</tr>
			{/section}
		</table>
	</div>
</div>
{/section}