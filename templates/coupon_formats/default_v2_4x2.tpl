{*
5/16/2011 4:56:41 PM Alex
- Change to compatabile with linux

6/9/2011 11:35:08 AM Alex
- add dash border

7/15/2011 1:11:05 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

10/20/2011 6:26:26 PM Alex
- change logo height to 150px and width to 200px

4/21/2017 10:38 AM Khausalya 
- Enhanced changes from RM to use config setting. 

5/8/2017 9:29 AM Khausalya
- Enhanced changes from Ringgit Malaysia to use config setting. 

12/2/2019 5:37 PM Andy
- Added Min Item Qty, Min Item Amount and Min Receipt Amount in Coupon Printing.

*}

{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}

<script type="text/javascript">
var doc_no = '{$coupon_code}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}

{literal}
<script>
</script>
<style>
.margin_offset div {
	border:0px solid #000;
}
.margin_offset {
	margin-top: 0.9cm;
	margin-left: 0cm;
}

.sample {
	width: 100mm; //210mm;
	height: 60mm; //295mm;
}

.sample2{
	height: 60mm;
}

.sample tr{
	height:13mm; //73mm;
	border-bottom:1px dashed black;
}

.column_1{
	border-right:1px dashed black;
}

.branch
{
	font-size:20pt;
	margin-top:0.50in;
	margin-left:0.30in;
	height:0.5in;
}

.amount_str
{
	font-size:10pt;
	color: #fff;
	text-align: center;
	/*margin-top:1.52in;*/
	/*margin-top:0.08in;*/
	margin-right:0.05in;
	/*width:11cm;*/

}

.amount
{
	font-size:30pt;
	color: #fff;
	margin-top:0.05in;
	margin-right:0in;
	/*width:5cm;*/
	line-height:25pt;
	font-weight: bold;
}

.date
{
	margin-left:0.1in;
	margin-bottom:0px;
}

.code
{
	font-size:8pt;
	margin-top:0.14in;
	margin-left:0.3in;
	/*height:0.46in;*/
	/*line-height:25pt;*/
}

.barcode {
	font-family: "MRV Code39extMA", verdana, calibri;
	font-size: 13pt;
	float: right;
	margin-right:3mm;
	padding-top: 10px;
}

.coupon_code{.
	margin-top:0.1in;
	height:10mm;//20mm;
}

.coupon_title{
	font-size:26pt;
	font-weight: bold;
	color: #e3b158;
	text-align:right;
	//margin-right:18mm;
	margin: 50px 0 0 0;
}

.small{
	font-size:8pt;
}

.couponbg{
	background: url('ui/coupon-bg.png') no-repeat;
	background-size: cover;
}

@page {
  size: A4;
  margin: 0.5in;
}

@media print {

html{
	zoom: 90%;
}
html, body {
    width: 210mm;
    height: 297mm;
  }

	.couponbg{
		background: url('ui/coupon-bg.png') no-repeat;
		background-size: cover;
	}
}

</style>
{/literal}
{assign var=total_pcs value=1}
{section name=qty loop=$pages.sheet}
	<div class=printarea>
		<div class=margin_offset>
			<table class="sample" style="border:1px solid black;border-collapse:collapse;">
			    {section name=row loop=$pages.row}
			    <tr {if $smarty.section.row.iteration eq $smarty.section.row.max}style="border-bottom:0px solid white;"{/if}>
				    {section name=column loop=$pages.column}
			        <td class="column_{$smarty.section.column.iteration} couponbg" width="50%" style="padding:0px 8px;">
			            <div style="margin-top:0.1in;height:98%;">
			            	{if $total_pcs <= $pages.pcs}
							{foreach name=calc from=$coupon item=cou}
							<table width="100%" class="sample">
								<tr>
									<td style="background-color: #e3b158">
										<div>
											<div style="height:40%;margin-top:15px; margin-bottom:15px; width:100%">
											   {* <div style="float:left;" class=amount>
											        Disc.
											    </div> *}
												<div style="text-align: center;margin-right:2mm;" class=amount>
												    {if $cou.print_type eq 'amount'}
														{$config.arms_currency.symbol} {$cou.coupon_value}
													{else}
														{$cou.coupon_value}%
													{/if}
												</div>
												{*<br style="clear:both;">*}
												<div style="margin-right:2mm;" class=amount_str>
													<b>	{if $cou.print_type eq 'amount'}
															{$config.arms_currency.name} {convert_number number=$cou.coupon_value show_decimal=1}
														{else}
															{convert_number number=$cou.coupon_value show_decimal=1 show_percentage=1}
														{/if}
														only
													</b>
												</div>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<td valign=middle>
										<div style="width: 100%">
											<div style="width: 55%; float:left">
												<div class='date small'>
													<ul style="margin-left:-0.3in;">
														<li>Can only be used for {if $cou.dept_id ne "0"}<b>{$cou.dept_description}</b> department with {/if}
														{if $cou.brand_description}
															<b>{$cou.brand_description}</b> brand
														{else}
															<b>{$cou.vendor_description}</b> vendor
														{/if}
														    items.
														</li>
													    <li>Available Time: {$cou.time_from|date_format:"%I:%M %p"} ~ {$cou.time_to|date_format:"%I:%M %p"}</li>
														<li>Valid From {$cou.valid_from} to {$cou.valid_to}</li>
														{if $cou.min_qty>0 or $cou.min_amt>0 or $cou.min_receipt_amt>0}
															<li>
																{if $cou.min_qty>0}
																	Min Item Qty: {$cou.min_qty}&nbsp;&nbsp;&nbsp;&nbsp;
																{/if}
																{if $cou.min_amt>0}
																	Min Item Amount: {$cou.min_amt|number_format:2}&nbsp;&nbsp;&nbsp;&nbsp;
																{/if}
																{if $cou.min_receipt_amt>0}
																	Min Receipt Amount: {$cou.min_receipt_amt|number_format:2}&nbsp;&nbsp;&nbsp;&nbsp;
																{/if}
															</li>
														{/if}
														<li>Not exchangeable for Cash</li>
														{if $cou.remark}<li>{$cou.remark}</li>{/if}
													</ul>
												</div>
											</div>
											<div style="width: 45%; float:right">
												<div class="coupon_title"><b>COUPON</b></div>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div style="width: 100%">
											<div class="coupon_code" style="width: 50%; float: left">
												<img src="{get_logo_url}" height="35px" max-width="auto">
											</div>
											<div class="coupon_code" style="width: 50%; float:right">
												<div class="barcode">*{$cou.barcode_coupon_prefix}{$cou.secur_barcode}*</div>
											</div>
										</div>
									</td>
								</tr>
							</table>

							{/foreach}
							{assign var=total_pcs value=$total_pcs+1}
							{else}
								<table width="100%" class="sample">
									<tr>
									<td></td>
									</tr>
								</table>
							{/if}
						</div>
						<br style="clear:both">
				    </td>
				    {/section}
				</tr>
				{/section}
			</table>
		</div>
	</div>
{/section}
