{*
5/16/2011 4:56:41 PM Alex
- Change to compatabile with linux

6/9/2011 11:35:08 AM Alex
- add dash border

7/15/2011 3:18:10 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

10/20/2011 6:26:26 PM Alex
- change logo height to 150px and width to 200px

10/28/2011 2:11:55 PM Alex
- add format change able to set preprinted and without cutting_line

4/21/2017 10:10 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/8/2017 9:54 AM Khausalya
- Enhanced changes from Ringgit Malaysia to use config setting. 

07/16/2020 11:21 Sheila
- Redesign template.
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
	margin-top: 0.8cm;
	margin-left: 0.3cm;
}

.sample {
	width: 190mm;//210mm;
	height: 200mm;//280mm;
}

.sample2{
	height: 80mm;
}

.sample tr {
	height:10mm;
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
	font-size:45pt;
	//margin-top: 0.6in;
	//margin-right:0.1in;
	/*width:11cm;*/
	color: #fff;
	font-weight: bold;
}

.amount_str
{
	font-size:13pt;
	color: #d5d5d5;
	//margin-top: -10px;
	//margin-right:0.1in;
	/*width:5cm;*/
}

.date
{
	font-size:13pt;
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
	font-size: 15pt;
	float: right;
	margin-right:10px;
}

.bud{
	width: 50mm;
	padding: 5px;
}

.voucher_code{.
	margin-top:0.2in;

}

.voucher_title{
	font-size:26pt;
	text-align:right;
	margin-right:10px;
	font-weight: bold;
	color: #424242;
}

.dash_right{
	border-right:1px dashed black;
}

.dash_bottom{
	border-bottom:1px dashed black;
}

.box{
  border: 3px solid #fff !important;
  padding: 5px;
  margin: 5px;
}

.box-amount{
	//text-align:right;
	//margin-top:0.2in;
	height:auto;
	text-align: center;
}

.voucherbg{
	background: url('ui/voucher-bg-greyscale.png') no-repeat;
	background-size: cover;
}

@page {
  size: A4;
  margin: 0.5in;
}

img {
  filter: grayscale(100%);
}

@media print {

 body {
    /* IE4-8 and 9 (deprecated). */
    filter: Gray();
    /* SVG version for IE10, Chrome 17, FF3.5, 
       Safari 5.2 and Opera 11.6 */
    filter: url('#grayscale'); 
    /* CSS3 filter, at the moment Webkit only. Prefix it for
       future implementations */
    -webkit-filter: grayscale(100%); 
    filter: grayscale(100%); /* future-proof */
  }

html{
	zoom: 90%;
}

html, body {
    width: 210mm;
    height: 297mm;
  }

	.voucherbg{
		background: url('ui/voucher-bg-greyscale.png') no-repeat;
		background-size: cover;
	}
}

</style>
{/literal}

{section name=page loop=$pages.sheet}
<div class=printarea>
	<div class=margin_offset >
		<table class=sample style="border:1px solid black;border-collapse:collapse">
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
					<td valign="top" class="voucherbg">
						<table class=sample2 width=100% height=100% style="border:0px solid black;border-collapse:collapse">
							{if $voucher[page][row].secur_barcode}
							  	<tr>
									<td valign="top" width=50%>
										<div style="margin-top:0.2in;height:100%;margin-left: 10px;">
											<div class="box-amount box">
												<div class=amount>{$config.arms_currency.symbol} {$voucher[page][row].voucher_value}</div>
												<div class=amount_str>{$config.arms_currency.name} {convert_number number=$voucher[page][row].voucher_value show_decimal=1} only</div>
											</div>
										</div>
									</td>
									<td>
									</td>
								</tr>
								<tr>
								<td>
									<div style="text-align:left; color: #fff">
										<div class=date>
											{if !$preprinted}
											Valid Until: <br>________________<br>
											Valid From: <br>________________
											{/if}
											{if $voucher[page][row].print_remark}<br><span style="font-size:8pt">*{$voucher[page][row].print_remark}</span>{/if}
											
										</div>
									</div>
								</td>
								<td rowspan="2">
										<div style="text-align:right;" class="voucher_title"><b>CASH</b></div>
										<div style="text-align:right;" class="voucher_title"><b>VOUCHER</b></div>
										{if !$preprinted}
										    <div style="text-align: right">
												<img src="{get_logo_url}" height="35px" max-width="auto" style="margin-right: 10px">
											</div>
										{/if}
									</td>
								</tr>
								<tr>
									<td></td>
								</tr>
								<tr>
									<td></td>
									<td>
										<div class=barcode>*{$voucher[page][row].barcode_voucher_prefix}{$voucher[page][row].secur_barcode}*</div>
									</td>
								</tr>
							{else}
								<tr>
								<td></td>
								</tr>
							{/if}
						</table>
					</td>
				</tr>
			{/section}
		</table>
	</div>
</div>
{/section}
