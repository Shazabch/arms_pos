{*
11/23/2011 12:29:12 PM Justin
- Added new report that similar as Sales Order Report to act as "Quotation".

1/8/2013 2:33 PM Justin
- Enhanced to show title as "Quotation / Warranty Form".

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

3/20/2015 4:06 PM Andy
- Enhance custom template to compatible with GST.
*}


{if !$skip_header}
{include file='header.print.tpl'}
<style>
{if $config.sales_order_printing_no_item_line}
{literal}
.no_border_bottom td{
	border-bottom:none !important;
}
.total_row td, .total_row th{
    border-top: 1px solid #000;
}
.td_btm_got_line td,.td_btm_got_line th{
    border-bottom:1px solid black !important;
}
{/literal}
{/if}

{literal}

{/literal}
</style>
<script type="text/javascript">
var doc_no = '{$form.order_no}';
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
<table width=100% cellspacing=0 cellpadding=0 border=0>
<tr class="small">
	<td><img src="{get_logo_url}" height=80 hspace=5 vspace=5></td>
	<td width=100%>
	<h2>{$from_branch.description}</h2>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}
		&nbsp;&nbsp; Fax: {$from_branch.phone_3}
		{/if}
		{if $config.enable_gst and $from_branch.gst_register_no}
			 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
		{/if}
	</td>
	<td rowspan=2 align=right>
	    <table class="xlarge">
			<tr>
				<td colspan=2>
					<div style="background:#000;padding:4px;color:#fff" align=center>
						<b>
							Quotation /<br />Warranty Form
						</b>
					</div>
				</td>
			</tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Order No.</td><td nowrap>{$form.order_no}</td></tr>
		    <tr height=22><td nowrap>Order Date</td><td nowrap>{$form.order_date|date_format:$config.dat_format}</td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
			<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td colspan="2">
	<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
	<tr>
		<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<h4>From </h4>
			<b>{$from_branch.description}</b><br>
			{$from_branch.address|nl2br}<br>
			Tel: {$from_branch.phone_1|default:"-"}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
			{if $from_branch.phone_3}<br>Fax: {$from_branch.phone_3}{/if}
		</td>

		<td valign=top style="border:1px solid #000; padding:5px">
			<h4>To</h4>
			<b>{$debtor[$form.debtor_id].description}</b><br>
			{$debtor[$form.debtor_id].address}<br>
		</td>

	</tr>
	</table>
</td>
</tr>
</table>

<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">

<tr bgcolor=#cccccc>
	<th rowspan=2 width=5>&nbsp;</th>
	<th rowspan=2 nowrap>ARMS Code</th>
	<th rowspan=2 nowrap>Article<br>/MCode</th>
	<th rowspan=2 width="90%">SKU Description</th>
	<th rowspan=2 width=40>RSP<br>(RM)</th>
	<th rowspan=2 width=40>UOM</th>
	<th nowrap colspan=2 width=80>Qty</th>
	<th colspan="2" width="60">Discount</th>
	
	{if $form.is_under_gst}
		<th rowspan="2">Gross Amt</th>
		<th rowspan="2">GST Code</th>
		<th rowspan="2">GST Amt</th>
	{/if}
	
	<th rowspan=2 width=40>Total<br>Amount</th>
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
	<th>Type</th>
	<th>Amt</th>
</tr>
{assign var=counter value=0}

{section name=i loop=$items}
<!-- {$counter++} -->
<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
    {assign var=row_amt value=$items[i].line_gross_amt+$items[i].line_gst_amt}
	{assign var=total_amt value=$total_amt+$row_amt}
	{assign var=total_ctn value=$total_ctn+$items[i].ctn}
	{assign var=total_pcs value=$total_pcs+$items[i].pcs}


	<td align="center" nowrap>{$start_counter+$counter}.</td>
	<td align="center" nowrap>{$items[i].sku_item_code}</td>
	<td align="center" nowrap>{$items[i].artno_mcode|default:'&nbsp;'}</td>
	<td width="90%"><div class="crop">{$items[i].sku_description}</div></td>
	<td align="right">{$items[i].selling_price|number_format:2}</td>
	<td align=center>{$items[i].uom_code|default:"EACH"}</td>
	<td align="right">{if strpos($items[i].ctn,'.')}{$items[i].ctn|qty_nf}{else}{$items[i].ctn|qty_nf}{/if}</td>
	<td align="right">{if strpos($items[i].pcs,'.')}{$items[i].pcs|qty_nf}{else}{$items[i].pcs|qty_nf}{/if}</td>
	
	<!-- Discount -->
	<td align="right">{$items[i].item_discount|default:'-'}</td>
	<td align="right">{$items[i].item_discount_amount|number_format:2}</td>
	
	{if $form.is_under_gst}
		<td align="right">{$items[i].line_gross_amt|number_format:2}</td>
		<td align="center">{$items[i].gst_code}({$items[i].gst_rate}%)</td>
		<td align="right">{$items[i].line_gst_amt|number_format:2}</td>
	{/if}
	
	<!-- Total Amount -->
    <td align="right">{$row_amt|number_format:2}</td>
</tr>
{/section}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height=20 class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
  	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
    <td>&nbsp;</td>
</tr>
{/section}

{if $is_lastpage}
	{assign var=sub_total_amt value=$form.total_amount+$form.sheet_discount_amount}
	
	<!-- Sub Total -->
	<tr class="total_row">
	    <th align="right" colspan="6" >Sub Total</th>
		{assign var=cols value=5}
		{if $form.is_under_gst}
			{assign var="total_gross_amount" value=$sub_total_amt-$form.total_gst_amt-$form.sheet_gst_discount}
			{assign var=cols value=$cols-4}
			<th colspan="4">&nbsp;</th>
			<th align="right">{$total_gross_amount|number_format:2}</th>
			<th align="right">&nbsp;</th>
			<th align="right">{$form.total_gst_amt+$form.sheet_gst_discount|number_format:2}</th>
		{/if}
		
		<th align="right" colspan="{$cols}">{$sub_total_amt|number_format:2}</th>
	</tr>
	
	<!-- Sheet Discount -->
	<tr>
	    <th align="right" colspan="6" >Discount</th>
	    <th align="right" colspan="2">{$form.sheet_discount|default:'&nbsp;'}</th>
		{assign var=cols value=3}
		{if $form.is_under_gst}
			{assign var=cols value=$cols-2}
			{math assign="sheet_discount_gross_amount" equation="x-y" x=$form.sheet_discount_amount y=$form.sheet_gst_discount}
			<th colspan="2">&nbsp;</th>
			<th align="right">{$sheet_discount_gross_amount|number_format:2}</th>
			<th align="right">&nbsp;</th>
			<th align="right">{$form.sheet_gst_discount|number_format:2}</th>
		{/if}
		<th align="right" colspan="{$cols}">{$form.sheet_discount_amount|number_format:2}</th>
	</tr>
	
	<!-- Total -->
	<tr>
	    <th align="right" colspan="6" >Total</th>
		<th align="right">{$form.total_ctn|qty_nf}</th>
		<th align="right">{$form.total_pcs|qty_nf}</th>
		
		{assign var=cols value=3}
		{if $form.is_under_gst}
			{assign var=cols value=$cols-2}
			<th colspan="2">&nbsp;</td>
			<th align="right">{$form.total_gross_amt|number_format:2}</th>
			<th align="right">&nbsp;</th>
			<th align="right">{$form.total_gst_amt|number_format:2}</th>
		{/if}
		
		<th align="right" colspan="{$cols}">{$form.total_amount|number_format:2}</th>
	</tr>
{assign var=total value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}
{/if}

</table>

{if $is_lastpage}
<br>
<b>Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.remark|default:"-"|nl2br}
</div>

<table width=100%>
<tr height=80>

<td valign=bottom class=small>
_________________<br>
Issued By<br>
Name: {$sessioninfo.fullname}<br>
Date:
</td>

<td valign=bottom class=small>
_________________<br>
Received By<br>
Name:<br>
Date:
</td>
</tr>
</table>
{/if}
<p align=center class=small>** This document is for reference purpose only **</p>

</div>
