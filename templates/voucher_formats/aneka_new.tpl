{*
4/28/2011 5:34:14 PM Alex
- create by me
*}

{config_load file="site.conf"}
{if !$skip_header}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<link rel="stylesheet" type="text/css" href="templates/print.css">

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
	margin-top: 1cm;
	margin-left: 0.2cm;
}

.sample {
	width: 210mm;
	height: 297mm;
}

.sample tr td {
	border:0px solid black;
}

.amount{
	font-family: calibri;
	font-size:67pt;

	/*width:11cm;*/
}

.amount2{
	font-family: calibri;
	font-size:48pt;
	margin-top:5px;
	margin-left:-2mm;
	/*width:11cm;*/
}

.small_amount{
	font-family: calibri;
	font-size:55pt;
	margin-right:-1mm;
}

.small_amount2{
	font-family: calibri;
	font-size:40pt;
	margin-top:5px;
	margin-left:-2mm;
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
	font-size: 9pt;
	margin-top:0mm;
	margin-left:10mm;
}

.bud{
	width: 40mm;
}

.currency{
	font-size:20px;
	margin-left:5mm;
}

</style>
{/literal}

{section name=page loop=$pages.sheet}
<div class=printarea>
	<div class=margin_offset >
		<table class=sample style="cellpadding:0;border-collapse:collapse">
			{section name=row loop=$pages.row}
			    <tr style="height:74mm">
			        <td valign="top" class="bud">
			            <div style="margin-top:0.3in;">
			            {if $bud[page][row].barcode}
							{$bud[page][row].barcode}
							<p>
					            Branch: {$bud[page][row].branch_code|upper}<br>
				            </p>
							<p>
								Value: RM {$bud[page][row].amount}<br>
							</p>
				            <p>
								Issue Date: ___________<br>
							</p>
							<p>
								Valid Until: ____________<br>
							</p>
							<p>
								Issue By: _____________<br>
							</p>
						{else}
							&nbsp;
						{/if}
						</div>
			        </td>
			        <td style="width:110mm">
			        	&nbsp;
			        </td>
					<td valign="top" style="width:60mm;">
			            <div style="margin-top:20mm;margin-left:-2mm;">
					    {if $voucher[page][row].secur_barcode}
							<div class="currency">
								<b>RM</b>
							</div>
							<div style="margin-top:-5mm;margin-left:3mm;">
								<table cellpadding="0" cellspacing="0"  style="width:55mm;border-collapse:collapse;">
								<tr>
									<td width="40mm" valign="top" align="right">
										<div class="{if $voucher[page][row].value_length > 2}small_amount{else}amount{/if}">{$voucher[page][row].ringgit}.</div></td>
									<td width="20mm" valign="top" align="right"><div class="{if $voucher[page][row].value_length > 2}small_amount2{else}amount2{/if}">{$voucher[page][row].cent}</div></td>
								</tr>
								</table>
							</div>
							<div class=barcode>*{$voucher[page][row].barcode_voucher_prefix}{$voucher[page][row].secur_barcode}*</div>

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
