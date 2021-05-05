{*
3/12/2012 7:10:00 PM Alex
- new smo format
3/13/2012 9:55:53 AM Alex
- make amount and amount_str to text-align center
- adjust size of barcode to fit with smo size
3/14/2012 2:42:30 PM Alex
- reduce size of date Zand adjust to fit smo
*}

{if !$skip_header}
{include file='header.print.tpl'}

<body onload="window.print()">
{/if}
{literal}
<script>
</script>
<style>
.margin_offset div {
	border:0px solid #000;
}
.margin_offset {
	margin-top: 0cm;
	margin-left: 0cm;
}

.sample {
	width: 210mm;
	height: 280mm;
}

.table_height{
	width: 210mm;
	height: 278mm;
}

.branch
{
	font-size:20pt;
	margin-top:0.50in;
	margin-left:0.30in;
	height:0.5in;
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

.date
{
	font-size:15pt;
	margin-left:0.1in;
}

.barcode {
	font-family: "MRV Code39extMA", verdana, calibri;
	font-size: 7pt;
}

.voucher_code{
	position:absolute;top:0mm;left:0mm
}

.voucher_title{
	font-size:10pt;
	text-align:center;
}

.title_barcode{
	position:absolute;top:17mm;height:50mm;
}

.amount_data{
	position:absolute;width:45mm;top:13mm;
}

.date_setting{
	position:absolute;width:45mm;top:30mm;
	font-size:8pt;
}
</style>
{/literal}

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
									<div class=amount>RM {$voucher[page][row][$col].voucher_value}</div>
									<div class=amount_str>Ringgit Malaysia {convert_number number=$voucher[page][row][$col].voucher_value show_decimal=1} only</div>
								</div>
								<div class="date_setting">
									Valid From:<br />
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
									<div class=amount>RM {$voucher[page][row][$col].voucher_value}</div>
									<div class=amount_str>Ringgit Malaysia {convert_number number=$voucher[page][row][$col].voucher_value show_decimal=1} only</div>
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
