{*
5/13/2011 2:49:25 PM Alex
- create by me to separate linux and windows to fix printing margin 

5/20/2011 11:13:38 AM Alex
- Check voucher value, if 2 digit or below big font size, else small

5/26/2011 2:14:04 PM Alex
- change follow new given format 

7/15/2011 3:18:42 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

9/22/2011 3:21:33 PM Alex
- add valid_from data
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
	margin-top: -0.2cm;
	margin-left: 0cm;
}

.sample {
	width: 210mm;
	height: 297mm;
}

.sample tr td {
	border:0px solid black;
}

.amount{
	font-family: impact;
	font-size:61pt;
	/*width:11cm;*/
}

.amount2{
	font-family: impact;
	font-size:70pt;
	/*width:11cm;*/
}

.barcode {
	font-family: "MRV Code39extMA", verdana, calibri;
	font-size: 9pt;
	position:absolute;
	left:1mm;
	bottom:3mm;
}

.bud{
	font-family: arial;
	font-size:8pt;
	width: 40mm;
}

.currency{
	font-family: arial;
	font-size:15pt;
}

</style>
{/literal}

{section name=page loop=$pages.sheet}
<div class=printarea>
	<div class=margin_offset >
		<table class=sample style="cellpadding:0;border-collapse:collapse">
			{section name=row loop=$pages.row}
			    <tr style="height:75mm">
			        <td valign="top" class="bud">
			            <div style="position:relative;top:0mm;left:2mm;height:98%">
			            {if $bud[page][row].barcode}
			            	<div style="position:absolute;top:24mm;">
							<p>
					            Branch: {$bud[page][row].branch_code|upper}
				            </p>
							<p>
								Value: RM {$bud[page][row].amount}
							</p>
				            <p>
								Issue Date: ___________
							</p>
							<p>
								Valid From: ___________
							</p>
							<p>
								Valid Until: ____________
							</p>
							<p>
								Issue By: _____________
							</p>
							</div>
							<div style="position:absolute;bottom:5mm;">
								{$bud[page][row].barcode}
							</div>
						{else}
							&nbsp;
						{/if}
						</div>
			        </td>
			        <td style="width:112mm">
			        	&nbsp;
			        </td>
					<td valign="top" style="width:58mm;">
			            <div style="position:relative;top:26mm;height:45mm">
					    {if $voucher[page][row].secur_barcode}
							<div class="currency">
								<b>RM</b>
							</div>
							<div style="position:absolute;text-align:right;right:8mm;bottom:15mm" class="amount">{$voucher[page][row].ringgit}.{$voucher[page][row].cent}</div>
							<div class=barcode style="position:absolute;left:1mm;bottom:3mm">*{$voucher[page][row].barcode_voucher_prefix}{$voucher[page][row].secur_barcode}*</div>
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
