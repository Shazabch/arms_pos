{*
7/15/2011 1:16:35 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

4/4/2012 10:54:00 AM Andy
- Change email address.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

10/1/2014 11:46 AM Justin
- Change the label from "Less Profit" to "Discount".

1/21/2015 10:46 AM Justin
- Enhanced to have GST calculation.

3/31/2015 6:24 PM Justin
- Enhanced to show GST registration no.

4/3/2015 5:54 PM Justin
- Enhanced to have GST summary and report changes.

4/20/2016 10:43 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/22/2017 1:47 PM Justin
- Enhanced company address to use from Masterfile Branch instead of HARDCODED it.

8/28/2018 11:56 AM Justin
- Enhanced to show/hide Tax Invoice caption base on document's GST status.
*}
<!-- this is the print-out for approved consignment invoice for cutemaree -->
{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
	
<style>
{literal}
.hd {
	background-color:#ddd;
}
.rw {
	background-color:#fff;
}
.rw2 {
	background-color:#eee;
}
.ft {
	background-color:#eee;
}

.header_tbl td{
    border:1px solid black;
	padding:3px;
}
{/literal}
</style>
<script type="text/javascript">
var doc_no = '{$form.ci_no}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}

<!-- print sheet -->
<div class=printarea>
<div style="text-align:center;" class="small">
	<img src="/cutemaree/cutemaree.jpg" height="70" /><br />
	<b>(Co No.: {$from_branch.company_no}{if $from_branch.gst_register_no}, GST Registration No.: {$from_branch.gst_register_no}{/if})</b><br />
	{$from_branch.address}<br />
	Tel: {$from_branch.phone_1|default:'-'}, {$from_branch.phone_2|default:'-'} &nbsp;&nbsp;&nbsp; Fax: {$from_branch.phone_3|default:'-'} &nbsp;&nbsp;&nbsp; E-mail: {$from_branch.contact_email}<br />
	{if $form.is_under_gst}
		<h2>TAX INVOICE</h2>
	{/if}
</div>

<table class="header_tbl" cellspacing="10px" cellpadding="4" width=100%>
	<tr>
	    <!-- Bill To-->
	    <td width="50%" class="small">
	        	<h3>&nbsp;&nbsp;&nbsp;Bill To</h3>
	        	<b>{$to_branch.description}</b><br>
				{$to_branch.address|nl2br}<br>
				{if $to_branch.gst_register_no}GST Registration No.: {$to_branch.gst_register_no}<br />{/if}
				Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
				{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
	    </td>
	    <td width="30%" border="1" class="small">
	            Date: <b>{$form.ci_date|date_format:"%d/%m/%Y"}</b><br /><br />
	            Department: <b>{$to_branch.con_dept_name|default:'-'}</b><br /><br />
	            Your/Our Purchase<br />
	            Order No: <b>CONSIGNMENT</b>
	    </td>
	    <td width="20%">
	        Invoice No: <b>{$form.ci_no}</b><br />
	        
	        Terms: <b>{$to_branch.con_terms|default:'-'}</b><br />
	        {$page}
	    </td>
	</tr>
</table>
<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">

<tr bgcolor=#cccccc>
	<th rowspan=2 width=5>&nbsp;</th>
	<th rowspan=2 width=100>Article</th>
	<th rowspan=2>SKU Description</th>
	<th rowspan=2 width=40>UOM</th>
	<th nowrap colspan=2 width=80>Qty</th>
	<th rowspan=2 width=40>Selling<br>({$to_branch.currency_code})</th>
    <th rowspan=2 width=40>Discount<br>(%)</th>
    <th rowspan=2 width=40>Amount<br>({$to_branch.currency_code})</th>
	{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
		<th rowspan=2 width=40>Amount<br>({$config.arms_currency.symbol})</th>
	{/if}
	{if $form.is_under_gst}
		<th rowspan="2" width=80>GST<br>({$config.arms_currency.symbol})</th>
		<th rowspan="2" width=80>Amount <br>Incl. GST ({$config.arms_currency.symbol})</th>
	{/if}
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>
{assign var=counter value=0}
{if $form.exchange_rate}
	{assign var=exchange_rate value=$form.exchange_rate}
{else}
	{assign var=exchange_rate value=1}
{/if}
{section name=i loop=$ci_items}
<!-- {$counter++} -->
<tr>
	<td align=center>{$start_counter+$counter}.</td>
	<td>{$ci_items[i].artno|default:"-"}</td>
	<td><div class="crop">{$ci_items[i].description}</div></td>
	
	<td align=center>{$ci_items[i].uom_code|default:"EACH"}</td>
	<td align="right">{$ci_items[i].ctn}</td>
	<td align="right">{$ci_items[i].pcs}</td>
	<td align="right">{$ci_items[i].cost_price|number_format:2}</td>
	{assign var=amt_ctn value=$ci_items[i].cost_price*$ci_items[i].ctn}
	{assign var=amt_pcs value=$ci_items[i].cost_price/$ci_items[i].uom_fraction*$ci_items[i].pcs}
	{assign var=total_row value=$amt_ctn+$amt_pcs}
	
	{if $form.show_per}
	    {if $ci_items[i].disc_arr.0}
		    {assign var=discount_per value=$ci_items[i].disc_arr.0*0.01}
			{assign var=discount_amount value=$total_row*$discount_per}
			{assign var=total_row value=$total_row-$discount_amount}
		{/if}
		{if $ci_items[i].disc_arr.1}
		    {assign var=discount_per value=$ci_items[i].disc_arr.1*0.01}
			{assign var=discount_amount value=$total_row*$discount_per}
			{assign var=total_row value=$total_row-$discount_amount}
		{/if}
	{/if}
	
	<td align="right">{$ci_items[i].discount|default:'0'}%</td>
	{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
		{if $form.is_under_gst}
			{assign var=total2_row value=$ci_items[i].item_foreign_gst_amt}
		{else}
			{assign var=total2_row value=$total_row/$exchange_rate}
		{/if}
		<td align="right">{$total2_row|number_format:3}</td>
	{/if}
	<td align="right">{$total_row|number_format:2}</td>
	{if $form.is_under_gst}
		<td align="right">{$ci_items[i].item_gst|number_format:2}</td>
		<td align="right">{$ci_items[i].item_gst_amt|number_format:2}</td>
	{/if}
	
	{assign var=total value=$total+$total_row|round2}
	{assign var=total2 value=$total2+$total2_row|round:3}
	{assign var=total_ctn value=$ci_items[i].ctn+$total_ctn}
	{assign var=total_pcs value=$ci_items[i].pcs+$total_pcs}
	{if $form.is_under_gst}
		{assign var=total_gst value=$total_gst+$ci_items[i].item_gst}
		{assign var=total_gst_amt value=$total_gst_amt+$ci_items[i].item_gst_amt}
	{/if}
</tr>
{/section}

{repeat s=$counter+1 e=$PAGE_SIZE}
<!-- filler -->
<tr height=20>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
		<td>&nbsp;</td>
	{/if}
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/repeat}

{if $is_lastpage}
<tr>
	<td colspan="6" rowspan="4" valign="top">
	    <b>Remark</b><br />
	    {$form.remark|default:"-"|nl2br}
	</td>
	<td colspan="2" align="right" >Gross Total</td>
	{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
		<td align="right" class="large" style="font-weight:bold;">{$total2|number_format:3}</td>
	{/if}
	<td align="right" class="large" style="font-weight:bold;">{$total|number_format:2}</td>
	{if $form.is_under_gst}
		<td align="right" class="large" style="font-weight:bold;">{$total_gst|number_format:2}</td>
		<td align="right" class="large" style="font-weight:bold;">{$total_gst_amt|number_format:2}</td>
	{/if}
</tr>
{if $form.discount_amount}
	{if $form.is_under_gst}
		{assign var=disc_arr value="+"|explode:$form.discount_percent}
		{foreach from=$disc_arr item=disc}
			{assign var=discount_amt value=$total*$disc*0.01}
			{assign var=gross_discount_amt value=$gross_discount_amt+$discount_amt}
			{assign var=total value=$total-$discount_amt}
		{/foreach}	
	{else}
		{assign var=gross_discount_amt value=$form.discount_amount}
		{assign var=total value=$total-$gross_discount_amt|round2}
	{/if}
{/if}
<tr>
	<td colspan="2" align="right">Discount ({$form.discount_percent}%)</td>
	{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
		<td align="right" class="large" style="font-weight:bold;">{$form.foreign_discount_amount|number_format:3}</td>
	{/if}
	{if $form.is_under_gst}
		<td class="large" style="font-weight:bold;" align=right>{$form.gross_discount_amount|number_format:2}</td> 
		<td class="large" style="font-weight:bold;" align=right>{$form.sheet_gst_discount|number_format:2}</td> 
	{/if}
	<td align="right" class="large" style="font-weight:bold;">{$form.discount_amount|number_format:2}</td>
</tr>
<tr>
	<td colspan="2" align="right">Sub Total</td>
	{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
		<td align="right" class="large" style="font-weight:bold;">{$form.total_foreign_amount|number_format:3}</td>
	{/if}
	{if $form.is_under_gst}
		<td class="large" style="font-weight:bold;" align=right>{$form.total_gross_amt|number_format:2}</td> 
		<td class="large" style="font-weight:bold;" align=right>{$form.total_gst_amt|number_format:2}</td> 
	{/if}
	<td align="right" class="large" style="font-weight:bold;">{$form.total_amount|number_format:2}</td>
</tr>
<tr bgcolor=#cccccc>
	<td colspan="2" align="right">Nett Total</td>
	{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
		<td align="right" class="large" style="font-weight:bold;">{$form.total_foreign_amount|number_format:3}</td>
	{/if}
	{if $form.is_under_gst}
		<td class="large" style="font-weight:bold;" align=right>{$form.total_gross_amt|number_format:2}</td> 
		<td class="large" style="font-weight:bold;" align=right>{$form.total_gst_amt|number_format:2}</td> 
	{/if}
	<td align="right" class="large" style="font-weight:bold;">{$form.total_amount|number_format:2}</td>
</tr>
{/if}
</table>

{if $is_lastpage}
<br>
    <table width=100%>
	<tr height=80>

	{if $form.is_under_gst}
		<td valign=top style="padding-right:10px;" width="30%">
			{include file='consignment.gst_summary.tpl'}
		</td>
	{/if}
	
	<td valign=bottom class=small>
	_________________<br>
	Issued By<br>
	Name: {$sessioninfo.fullname}<br>
	Date:
	</td>

	<td valign=bottom class=small>
	_________________<br>
	Approved By<br>
	Name:<br>
	Date:
	</td>
	</tr>
	</table>
	
	{if !$form.approved}
		<p align=center class=small>** This document is for reference purpose only **</p>
	{/if}
{/if}
</div>
