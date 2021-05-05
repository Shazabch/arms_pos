{*
5/23/2012 2:04:34 PM Justin
- Removed the Valid From and To.
- Re-aligned the barcode form to stick into middle of each box.
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
	margin-top: -1mm;
	margin-left: 0cm;
}

.sample {
	width: 220mm;
	height: 278mm;
}

.table_height{
	width: 220mm;
	height: 278mm;
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
	position:relative;
	padding-top:15px;
	top:0mm;
	left:0mm;
}

.voucher_title{
	font-size:10pt;
	text-align:center;
}

.title_barcode{
	position:relative;
}

.amount_data{
	position:relative;
	left:7mm !important;
	text-align:center;
}

.date_setting{
	position:relative;
	font-size:8pt;
	left:9mm;
	top:1mm;
	text-align:left !important;
}
</style>
{/literal}

{section name=page loop=$pages.sheet}
<div class=printarea>
	<div class="margin_offset sample" >
		<table class='table_height' style="border:0px solid black;border-collapse:collapse">
			{section name=row loop=$pages.row}
			    <tr style="border-top:1px dotted black;border-collapse:collapse;">
					<td valign="top">
						<div style="position:relative; !important;">
				            {assign var=col value=0}
				            <div class="title_barcode" style="text-align:center; height:3.9cm; width:5.40cm; border-right:1px dotted black !important; float:left;">
				            {if $voucher[page][row][$col].secur_barcode}
								<div class="voucher_code">
									<div class="voucher_title"><b>Cash Voucher</b></div>
									<div class=barcode>*{$voucher[page][row][$col].barcode_voucher_prefix}{$voucher[page][row][$col].secur_barcode}*</div>
								</div>
								<div class="amount_data" style="left:0mm;">
									<div class=amount>RM {$voucher[page][row][$col].voucher_value}</div>
									<div class=amount_str>Ringgit Malaysia {convert_number number=$voucher[page][row][$col].voucher_value show_decimal=1} only</div>
								</div>
							{else}
							    &nbsp;
							{/if}
							</div>
							
							{assign var=col value=1}
				            <div class="title_barcode" style="text-align:center; height:3.9cm; width:5.40cm; border-right:1px dotted black !important; float:left;">
				            {if $voucher[page][row][$col].secur_barcode}
								<div class="voucher_code">
									<div class="voucher_title" style="top:10mm;left:10mm;"><b>Cash Voucher</b></div>
									<div class="barcode">*{$voucher[page][row][$col].barcode_voucher_prefix}{$voucher[page][row][$col].secur_barcode}*</div>
								</div>
								<div class="amount_data" style="left:0mm;">
									<div class=amount>RM {$voucher[page][row][$col].voucher_value}</div>
									<div class=amount_str>Ringgit Malaysia {convert_number number=$voucher[page][row][$col].voucher_value show_decimal=1} only</div>
								</div>
							{else}
							    &nbsp;
							{/if}	
							</div>
							{assign var=col value=2}
				            <div class="title_barcode" style="text-align:center; height:3.9cm; width:5.40cm; border-right:1px dotted black !important; float:left;">
				            {if $voucher[page][row][$col].secur_barcode}
								<div class="voucher_code">
									<div class="voucher_title" style="top:10mm;left:10mm;"><b>Cash Voucher</b></div>
									<div class="barcode">*{$voucher[page][row][$col].barcode_voucher_prefix}{$voucher[page][row][$col].secur_barcode}*</div>
								</div>
								<div class="amount_data" style="left:0mm;">
									<div class=amount>RM {$voucher[page][row][$col].voucher_value}</div>
									<div class=amount_str>Ringgit Malaysia {convert_number number=$voucher[page][row][$col].voucher_value show_decimal=1} only</div>
								</div>
							{else}
							    &nbsp;
							{/if}	
							</div>
							{assign var=col value=3}
				            <div class="title_barcode" style="text-align:center; height:3.9cm; width:5.40cm; border-right:1px dotted black !important; float:left;">
				            {if $voucher[page][row][$col].secur_barcode}
								<div class="voucher_code">
									<div class="voucher_title" style="top:10mm;left:10mm;"><b>Cash Voucher</b></div>
									<div class="barcode">*{$voucher[page][row][$col].barcode_voucher_prefix}{$voucher[page][row][$col].secur_barcode}*</div>
								</div>
								<div class="amount_data" style="left:0mm;">
									<div class=amount>RM {$voucher[page][row][$col].voucher_value}</div>
									<div class=amount_str>Ringgit Malaysia {convert_number number=$voucher[page][row][$col].voucher_value show_decimal=1} only</div>
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