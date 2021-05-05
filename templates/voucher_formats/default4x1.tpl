{*
11/22/2016 5:32 PM Andy
- Added new default printing format (ARMS 4 x 1).

4/21/2017 10:14 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/8/2017 10:01 AM Khausalya
- Enhanced changes from Ringgit Malaysia to use config setting. 

9/11/2020 11:40 AM William
- Bug fixed voucher printing alignment issue.
*}


{if !$skip_header}
{include file='header.print.tpl'}

<script type="text/javascript">
	document.title = '{$filename}';
</script>

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
	margin-top: 0.2cm;
	margin-left: 0.3cm;
}

.sample {
	width: 210mm;
	height: 290mm;
}

.sample tr {
	height:73mm;
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
	font-size:52pt;
	margin-top: 0.6in;
	margin-right:0.1in;
	/*width:11cm;*/
}

.amount_str
{
	line-height: 22px;
	font-size:20pt;
	/*margin-top: -0.2in;*/
	margin-right:0.1in;
	/*width:5cm;*/
}

.date
{
	font-size:15pt;
	margin-left:0.1in;
}

.code
{
	font-size:14pt;
	margin-top:0.14in;
	margin-left:0.3in;
	/*height:0.46in;*/
	/*line-height:25pt;*/
}

.barcode {
	font-family: "MRV Code39extMA", verdana, calibri;
	font-size: 10pt;
	float: right;
	margin-right:0.3in;
}

.bud{
	width: 50mm;
}

.voucher_code{.
	margin-top:0.2in;

}

.voucher_title{
	font-size:14pt;
	text-align:right;
	margin-right:0.7in;
}

.dash_right{
	border-right:1px dashed black;
}

.dash_bottom{
	border-bottom:1px dashed black;
}

</style>
{/literal}

{section name=page loop=$pages.sheet}
<div class=printarea>
	<div class=margin_offset >
		<table class=sample style="border:0px solid black;border-collapse:collapse">
			{section name=row loop=$pages.row}
			    <tr {if $smarty.section.row.iteration eq $smarty.section.row.max}style="border-bottom:0px solid white;"{/if} class="{if !$no_cutting_line}dash_bottom{/if}">
			        <td valign="top" class="bud {if !$no_cutting_line}dash_right{/if}">
			            <div style="margin-top:0.2in;height:100%;">
			            {if $bud[page][row].barcode}
				            Print by: {$bud[page][row].print_by}<br>
				            Timestamp: {$bud[page][row].timestamp}<br>
							Valid From: <br>
							Valid to: <br>
							Amount: {$config.arms_currency.symbol} {$bud[page][row].amount}<br>
							Barcode: {$bud[page][row].barcode}<br>
							IP: {$bud[page][row].ip}<br>
							Register Time: {$bud[page][row].register_time}<br>
							Register By: {$bud[page][row].register_by}<br>
							print by branch: {$BRANCH_CODE}<br>
						{else}
							&nbsp;
						{/if}
						</div>
			        </td>
					<td valign="top">
			            <div style="margin-top:0.13in;height:100%;margin-right:3mm">
					    {if $voucher[page][row].secur_barcode}
							<div class="voucher_code">
							{if !$preprinted}
							    <div style="float:left;">
									<img src="{get_logo_url}" height="140px" width="180px">
								</div>
							{/if}
								<div class="voucher_title"><b>Cash Voucher</b></div>
								<div class=barcode>*{$voucher[page][row].barcode_voucher_prefix}{$voucher[page][row].secur_barcode}*</div>
							</div>
							<div style="text-align:right;margin-top:0.2in;height:50%;">
								<div class=amount>{$config.arms_currency.symbol} {$voucher[page][row].voucher_value}</div>
								<div class=amount_str>{$config.arms_currency.name} {convert_number number=$voucher[page][row].voucher_value show_decimal=1} only</div>
							</div>
							<div style="text-align:left;">
								<div class=date>
									Issue Date: ________________
									{if $voucher[page][row].print_remark}<br><span style="font-size:8pt">*{$voucher[page][row].print_remark}</span>{/if}
									
								</div>
							</div>
						{else}
						    &nbsp;
						{/if}
						</div>
					</td>
				</tr>
			{/section}
		</table>
	</div>
</div>
{/section}
