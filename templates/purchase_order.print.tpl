{*
REVISION HISTORY
++++++++++++++++
9/26/2007 4:23:30 PM gary
- remove Proforma PO No. from the report printing.

9/27/2007 4:06:45 PM gary
- change the proformal po msg. (request by ah lee)

10/17/2007 3:27:40 PM yinsee
- citymart request: highlight GP and Selling
- show terms and prompt payment discount

11/28/2007 12:54:21 PM gary
- change FOC color to lighter.

1/7/2008 6:00:03 PM gary
- set font as BOLD if selling price less than cost price and margin is negative.

1/9/2008 3:25:21 PM gary
- PO to indicate payment terms when printing.
*}

{config_load file="site.conf"}
{if !$skip_header}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<link rel="stylesheet" type="text/css" href="templates/print.css">
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

{/literal}
</style>

<body onload="window.print()">
{/if}

<!-- loop for each sheets -->
{assign var=sheet_n value=0}
{if $print.vendor_copy}
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td><img src="{get_logo_url mod='po'}" height=80 hspace=5 vspace=5></td>
	<td width=100%>
	<h2>{$billto.description}</h2>
	{$billto.address|nl2br}<br>
	Tel: {$billto.phone_1}{if $billto.phone_2} / {$billto.phone_2}{/if}
	&nbsp;&nbsp; Fax: {$billto.phone_3}
	</td>
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:2px;color:#fff" align=center><b>{if $form.status==0}Draft{elseif !$form.approved}Proforma{/if} Purchase Order</b></div>
{if $form.status==0}
<div class="xsmall">This draft PO is for internal use only, not valid for any purchase use.</div>
{elseif !$form.approved}
<div class="xsmall">This Proforma PO is not valid for delivery, official PO will be issued to supersede this document after approval.</div>
<!--div class="xsmall">This Proforma PO is waiting for approval, no delivery will be accepted until this PO is fully approved by the authority.</div-->
{/if}
		<br>
		</td></tr>
		<tr bgcolor="#eeeeee"><td nowrap>PO No.</td><td nowrap>{$form.po_no}</td></tr>
		{if $config.po_show_terms}<tr><td nowrap>Payment Terms</td><td nowrap>{$form.term|default:"-"} Days</td></tr>{/if}
	    <tr><td nowrap>Department</td><td nowrap>{$form.department}</td></tr>
		<tr><td nowrap>Ordered By</td><td nowrap>{$form.fullname}</td></tr>
		<tr><td nowrap>PO Date</td><td nowrap>{$form.po_date|date_format:"%d/%m/%Y"}</td></tr>
		
		<!--add vendor payment terms-->
		{if $form.payment_term}
		<tr>
		<td nowrap>Payment Term</td>
		<td nowrap>{$form.payment_term}</td>
		</tr>	
		{/if}	

		<tr bgcolor="#eeeeee"><td nowrap>Delivery Date</td><td nowrap>{$form.delivery_date}</td></tr>
		<tr bgcolor="#eeeeee"><td nowrap>Cancellation Date</td><td nowrap>{$form.cancel_date}</td></tr>

	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	<table cellspacing=5 cellpadding=0 border=0>
	<tr>
		<td valign=top width=50% style="border:1px solid #000; padding:5px">
		<h4>Vendor</h4>
		<b>{$vendor.description}</b><br>
		{$vendor.address|nl2br}<br>
		Tel: {$vendor.phone_1|default:"-"}{if $vendor.phone_2} / {$vendor.phone_2}{/if}
		{if $vendor.phone_3}<br>Fax: {$vendor.phone_3}{/if}
		</td>

		<td valign=top width=50% style="border:1px solid #000; padding:5px">
		<h4>Deliver To</h4>
		<b>{$deliver.description}</b><br>
		{$deliver.address|nl2br}<br>
		Tel: {$deliver.phone_1|default:"-"}{if $deliver.phone_2} / {$deliver.phone_2}{/if}
		{if $deliver.phone_3}<br>Fax: {$deliver.phone_3}{/if}
		</td>

	</tr>
	</table>
</td>
</tr>
</table>

<!--- item table -->
<div style="border:2px solid #000; padding:1px;">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="box small">
<tr class="hd topline">
	<th rowspan=2>No</th>
	<th rowspan=2 nowrap>Art/MCode<br>ARMS Code</th>
	<th width=100% rowspan=2 nowrap>SKU Description</th>
	{if $config.link_code_name}
	<th rowspan=2>{$config.link_code_name}</th>
	{/if}
	<th rowspan=2>Cost<br>Price</th>
	<th rowspan=2>UOM</th>
	<th colspan=2>Qty</th>
	<th colspan=2>FOC</th>
	<th rowspan=2>Gross<br>Amount</th>
	<th rowspan=2>Tax (%)</th>
	<th rowspan=2>Discount</th>
	<th rowspan=2>Nett<br>Amount</th>
</tr>
<tr class="hd topline xsmall">
	<th>Ctn</th>
	<th>Pcs</th>
	<th>Ctn</th>
	<th>Pcs</th>
</tr>
<tr>
<td {if $config.link_code_name}colspan=14{else}colspan=13{/if} style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>

{assign var=counter value=0}
{foreach name=t from=$po_items[$sheet_n] item=item key=item_id}
{cycle name=rowbg values="#eeeeee,#dddddd" assign=rowbg}
{if $item.qty+$item.qty_loose+$item.foc+$item.foc_loose>0}
<!-- {$counter++} -->
<tr height=30 class="rw{cycle name=row values=",2"}">
	<td>{$counter}</td>
	<td nowrap>{$item.artno_mcode|default:"&nbsp;"}
	<br>{$item.sku_item_code}</td>
	<td>
		{$item.description}
		{if $item.remark}<br><i>{$item.remark}</i>{/if}
	</td>
	{if $config.link_code_name}
	<td >{$item.link_code|default:"&nbsp;"}</td>
	{/if}
	{if $item.is_foc}
	<th>FOC</th>
	{else}
	<td nowrap align=right>{$item.order_price|number_format:3}</td>
	{/if}
	<td nowrap>{$item.order_uom|default:'EACH'}</td>
	<td nowrap>{$item.qty|ifzero:"&nbsp;"}</td>
	<td nowrap>{$item.qty_loose|ifzero:"&nbsp;"}</td>
	<td nowrap>{$item.foc|ifzero:"&nbsp;"}</td>
	<td nowrap>{$item.foc_loose|ifzero:"&nbsp;"}</td>

	{if $item.is_foc}
	<th>FOC</th>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<th>FOC</th>
	{else}
	<td nowrap align=right>{$item.gamount|number_format:2}</td>
	<td nowrap>{$item.tax|ifzero:"&nbsp;"}</td>
	<td nowrap>
		{if $item.disc_remark}
			{$item.disc_remark}<br>
			({$item.discount})
		{elseif $item.discount}
			{$item.discount}
			{if strstr($item.discount,"%") or $form.po_option}
				<br>({$item.disc_amount|number_format:2})
			{/if}
		{else}
		&nbsp;
		{/if}
	</td>
	<td nowrap align=right>{$item.amount|number_format:2}</td>
	{/if}
</tr>
{/if}
{/foreach}

{repeat s=$counter+1 e=15}
<!-- filler -->
<tr height=30>
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
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/repeat}

<td {if $config.link_code_name}colspan=14{else}colspan=13{/if} style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>

<!-- total -->
<tr height=30>
    <td {if $config.link_code_name}colspan=4{else}colspan=3{/if} rowspan=6 style="background:none" valign=top>
    <h4>Remark</h4>
	{$form.remark[$sheet_n]|nl2br}
	</td>
	<td class="ft" nowrap>
		<b class=small>T.Ctn</b><br>
		{$total[$sheet_n].ctn|number_format}
	</td>
	<td class="ft" nowrap>
		<b class=small>T.Unit</b><br>
		{$total[$sheet_n].foc+$total[$sheet_n].qty|number_format}
	</td>
	<td class="ft" colspan=4 align=right><b>Sub Total</b></td>
	<td class="ft" align=right>{$total[$sheet_n].gamount|number_format:2}</td>
	<td class="ft">&nbsp;</td>
	<td class="ft">&nbsp;</td>
	<td class="ft" nowrap align=right>{$total[$sheet_n].amount|number_format:2}</td>
</tr>

<!-- misc cost -->
{if $form.misc_cost[$sheet_n] ne '' && $form.misc_cost[$sheet_n] > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Miscellanous Cost</b></td>
	<td nowrap align=right>{$form.misc_cost[$sheet_n]}{$form.misc_cost_amount[$sheet_n]}</td>
</tr>
{/if}

<!-- final discount  -->
{if $form.sdiscount[$sheet_n] ne '' && $form.sdiscount[$sheet_n] > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Discount</b></td>
	<td nowrap align=right>{$form.sdiscount[$sheet_n]}
	{if strstr($form.sdiscount[$sheet_n],"%") or $form.sdiscount[$sheet_n] != $form.sdiscount_amount[$sheet_n]}
		({$total[$sheet_n].sdiscount_amount|number_format:2})
	{/if}
	</td>
</tr>
{/if}

<!-- "special" discount
{if $form.rdiscount[$sheet_n]}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Discount from Remark</b></td>
	<td nowrap align=right>{$form.rdiscount[$sheet_n]}{$form.rdiscount_amount[$sheet_n]}</td>
</tr>
{/if}
 -->


<!-- transportation cost -->
{if $form.transport_cost[$sheet_n] ne '' && $form.transport_cost[$sheet_n] > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Transport Charges</b></td>
	<td nowrap align=right>{$form.transport_cost[$sheet_n]|number_format:2}{$form.transport_cost_amount[$sheet_n]}</td>
</tr>
{/if}

<!-- final final amount -->
<tr class="ft2 topline">
	<td colspan=10 align=right>
		<h1>This PO Total:
		RM{$total[$sheet_n].final_amount2|number_format:2}
		</h1>
	</td>
</tr>

</table>
</div>

<br><br><br>

<table width=100%>
{if $config.po_external_copy_3signatures}
<tr>
<td width=33% valign=bottom class=small align=left>
_________________<br>
Order By<br>
Name:
</td>
<td  width=33% valign=bottom class=small align=left>
_________________<br>
Approved By<br>
Name:
</td>

<td width=33% valign=bottom class=small align=left>
_________________<br>
Accepted By<br>
Name:
</td>
</tr>
<tr>
<td colspan=3 height=15>&nbsp;</td>
</tr>
<tr>
<td width=50% valign=top class=xsmall>
<b>IMPORTANT:</b><br>
i) Please quote our P/O No. in all your Delivery Order / Invoices.<br>
ii) Kindly supply goods in strict accordance to our P/O.<br>
iii) Valid only if goods according to Trade Description.<br>
iv) Prices quality of merchandise if differ from original sample will be liable for deduction or return.
</td>

<td width=50% valign=top class=xsmall>
<b>NOTICE:</b><br>
NOTICE is hereby given that the undersigned Company will strictly abandond and reject any Supplier who has factoring arrangement with financial institution.
</td>
</tr>

{else}
<tr>
<td width=40% valign=bottom class=small>
______________________________<br>
Accepted By<br>
Name:
</td>
<td width=30% valign=top class=xsmall>
<b>IMPORTANT:</b><br>
i) Please quote our P/O No. in all your Delivery Order / Invoices.<br>
ii) Kindly supply goods in strict accordance to our P/O.<br>
iii) Valid only if goods according to Trade Description.<br>
iv) Prices quality of merchandise if differ from original sample will be liable for deduction or return.
</td>
<td width=30% valign=top class=xsmall>
<b>NOTICE:</b><br>
NOTICE is hereby given that the undersigned Company will strictly abandond and reject any Supplier who has factoring arrangement with financial institution.
</td>
</tr>
{/if}
</table>
</div>
{/if}

<!---------------------------------------------  BRANCH COPY -------------------------------------------------->
{if $print.branch_copy}
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td valign=bottom>
		<h2>{$billto.description}</h2>

		<table cellspacing=5 cellpadding=0 border=0>
		<tr>
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<h4>Vendor</h4>
			<b>{$vendor.description}</b><br>
			{$vendor.address|nl2br}<br>
			Tel: {$vendor.phone_1|default:"-"}{if $vendor.phone_2} / {$vendor.phone_2}{/if}
			</td>

			<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<h4>Deliver To</h4>
			<b>{$deliver.description}</b><br>
			{$deliver.address|nl2br}<br>
			Tel: {$deliver.phone_1|default:"-"}{if $deliver.phone_2} / {$deliver.phone_2}{/if}
			</td>

		</tr>
		</table>
	</td>
	<td rowspan=2 align=right valign=top>

	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:2px;color:#fff" align=center><b>{if $form.status==0}Draft{elseif !$form.approved}Proforma{/if} Purchase Order</b></div>
{if $form.status==0}
<div class="xsmall">This draft PO is for internal use only, not valid for any purchase use.</div>
{elseif !$form.approved}
<div class="xsmall">This Proforma PO is not valid for delivery, official PO will be issued to supersede this document after approval.</div>
<!--div class="xsmall">This Proforma PO is waiting for approval, no delivery will be accepted until this PO is fully approved by the authority.</div-->
{/if}
		<br>
		</td></tr>
	    <tr bgcolor="#cccccc"><td nowrap>PO No.</td><td nowrap>{$form.po_no}</td></tr>
		{if $config.po_show_terms}<tr><td nowrap>Payment Terms</td><td nowrap>{$form.term|default:"-"} Days</td></tr>{/if}
	    <tr><td nowrap>Department</td><td nowrap>{$form.department}</td></tr>
		<tr><td nowrap>Ordered By</td><td nowrap>{$form.fullname}</td></tr>
		<tr><td nowrap>PO Date</td><td nowrap>{$form.po_date|date_format:"%d/%m/%Y"}</td></tr>
		
		<!--add vendor payment terms-->
		{if $form.payment_term}
		<tr>
		<td nowrap>Payment Term</td>
		<td nowrap>{$form.payment_term}</td>
		</tr>	
		{/if}	
		
		
		<tr bgcolor="#cccccc"><td nowrap>Delivery Date</td><td nowrap>{$form.delivery_date}</td></tr>
		<tr bgcolor="#cccccc"><td nowrap>Cancellation Date</td><td nowrap>{$form.cancel_date}</td></tr>
	  	</table>
	</td>
</tr>
</table>

<!--- item table -->
<div style="border:2px solid #000; padding:1px;">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="box small">
<tr class="hd topline">
	<th rowspan=2>No</th>
	<th rowspan=2 nowrap>Art/MCode<br>ARMS Code</th>
	<th width=100% rowspan=2 nowrap>SKU Description</th>
	{if $config.link_code_name}
	<th rowspan=2>{$config.link_code_name}</th>
	{/if}
	<th style="background:#aaa">Selling</th>
	<th>UOM</th>
	<th colspan=2>Qty</th>
	<th colspan=2 style="background:#aaa">FOC</th>
	<th rowspan=2>Gross<br>Amount</th>
	<th rowspan=2>Tax<br>(%)</th>
	<th rowspan=2>Discount</th>
	<th nowrap>T.Selling</th>
	<th nowrap style="background:#aaa">GP</th>
</tr>
<tr class="hd topline">
	<th style="background:#aaa">Cost</th>
	<th>UOM</th>
	<th class="xsmall">Ctn</th>
	<th class="xsmall">Pcs</th>
	<th class="xsmall" style="background:#aaa">Ctn</th>
	<th class="xsmall" style="background:#aaa">Pcs</th>
	<th nowrap>Nett Amt</th>
	<th nowrap style="background:#aaa">Profit(%)</th>
</tr>
<tr>
<td {if $config.link_code_name}colspan=15{else}colspan=14{/if} style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>
{assign var=counter value=0}
{foreach name=t from=$po_items[$sheet_n] item=item key=item_id}
{cycle name=rowbg values="#ddd,#ccc" assign=rowbg}
{if $item.qty+$item.qty_loose+$item.foc+$item.foc_loose>0}
<!-- {$counter++} -->
<tr height=15 class="rw{cycle name=crow values=",2"}">
	<td rowspan=2>{$counter}</td>
	<td rowspan=2 nowrap>{$item.artno_mcode|default:"&nbsp;"}
	<br>{$item.sku_item_code}</td>
	<td rowspan=2>
		{if $item.is_foc}<sup>{$item.foc_id}</sup>{/if}
		{$item.description}
		<sup>{$foc_annotations.$item_id}</sup>
		{if $item.remark}<br><i>{$item.remark}</i>{/if}
		{if $item.remark2}<br><i>{$item.remark2}</i>{/if}
	</td>
	{if $config.link_code_name}
	<td rowspan=2>{$item.link_code|default:"&nbsp;"}</td>
	{/if}
	
	{assign var=cost value=$item.order_price/$item.order_uom_fraction}
	{assign var=sell value=$item.selling_price/$item.selling_uom_fraction}
	<td nowrap align=right style="background:{$rowbg};{if $sell<$cost}font-weight:bold;{/if}">
	{$item.selling_price|number_format:3}
	</td>
	<td nowrap>{$item.selling_uom|default:'EACH'}</td>
	<td rowspan=2 nowrap>{$item.qty|ifzero:"&nbsp;"}</td>
	<td rowspan=2 nowrap>{$item.qty_loose|ifzero:"&nbsp;"}</td>
	<td rowspan=2 nowrap style="background:{$rowbg}">{$item.foc|ifzero:"&nbsp;"}</td>
	<td rowspan=2 nowrap style="background:{$rowbg}">{$item.foc_loose|ifzero:"&nbsp;"}</td>

	{if $item.is_foc}
	<th rowspan=2>FOC</th>
	<td rowspan=2>&nbsp;</td>
	<td rowspan=2>&nbsp;</td>
	{else}
	<td rowspan=2 nowrap align=right>{$item.gamount|number_format:2}</td>
	<td rowspan=2 nowrap>{$item.tax|ifzero:"&nbsp;"}</td>
	<td rowspan=2 nowrap>
		{if $item.disc_remark}
			{$item.disc_remark}<br>
			({$item.discount})
		{elseif $item.discount}
			{$item.discount}
			{if strstr($item.discount,"%")}
				<br>({$item.disc_amount|number_format:2})
			{/if}
		{else}
		&nbsp;
		{/if}
	</td>
	{/if}
	<td nowrap align=right>{$item.total_selling|number_format:2}</td>
	{if $item.is_foc}
		{assign var=total_profit value=$item.total_selling}
	{else}
		{assign var=total_profit value=$item.total_selling-$item.amount}
	{/if}
	<td nowrap align=right style="background:{$rowbg}">{$total_profit|number_format:2}</td>
</tr>
<tr height=15 class="rw{cycle name=crow2 values=",2"}">
	{if $item.is_foc}
	<th style="background:{$rowbg};border-top:1px solid #000">FOC</th>
	{else}
	<td style="background:{$rowbg};border-top:1px solid #000" nowrap align=right>
	{$item.order_price|number_format:3}
	</td>
	{/if}
	<td style="border-top:1px solid #000" nowrap>{$item.order_uom|default:'EACH'}</td>
	{if $item.is_foc}
	<th style="border-top:1px solid #000">FOC</th>
	{else}
	<td style="border-top:1px solid #000" nowrap align=right>{$item.amount|number_format:2}</td>
	{/if}
	
	{assign var=gp value=$total_profit/$item.total_selling*100|number_format:2}
	<td style="background:{$rowbg};border-top:1px solid #000;{if $gp<0}font-weight:bold;{/if}" nowrap align=right>{if $item.total_selling<=0}-{else}{$gp}%{/if}</td>
</tr>
{/if}
{/foreach}

{repeat s=$counter+1 e=15}
<!-- filler -->
<tr height=30>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td style="background:{$rowbg}">&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td style="background:{$rowbg}">&nbsp;</td>
	<td style="background:{$rowbg}">&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td style="background:{$rowbg}">&nbsp;</td>
</tr>
{/repeat}

<td {if $config.link_code_name}colspan=15{else}colspan=14{/if} style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>

<!-- total -->
<tr height=30>
    <td {if $config.link_code_name}colspan=4{else}colspan=3{/if} rowspan=10 style="background:none" valign=top>
    <h4>Remark</h4>
	{$form.remark[$sheet_n]|nl2br}<br>
	<br>
    <h4>Remark#2 (For Internal Use)</h4>
	{$form.remark2[$sheet_n]|nl2br}
	</td>
	<td rowspan=2 class="ft" nowrap>
		<b class=small>T.Ctn</b><br>
		{$total[$sheet_n].ctn|number_format}
	</td>
	<td rowspan=2 class="ft" nowrap>
		<b class=small>T.Unit</b><br>
		{$total[$sheet_n].foc+$total[$sheet_n].qty|number_format}
	</td>
	<td rowspan=2 class="ft" colspan=4 align=right><b>Sub Total</b></td>
	<td rowspan=2 class="ft" align=right>{$total[$sheet_n].gamount|number_format:2}</td>
	<td colspan=2 class="ft" align=right><b>T.Amount</b></td>
	{assign var=total_profit value=$total[$sheet_n].sell-$total[$sheet_n].amount}
	<td class="ft" nowrap align=right>{$total[$sheet_n].amount|number_format:2}</td>
	<td class="ft" nowrap align=right>{$total_profit|number_format:2}</td>
</tr>

<tr class="ft topline">
	<td align=right colspan=2><b>T.Selling</b></td>
	<td align=right>{$total[$sheet_n].sell|number_format:2}</td>
	<td align=right>{if $total[$sheet_n].sell<=0}-{else}{$total_profit/$total[$sheet_n].sell*100|number_format:2}%{/if}</td>
</tr>

<!-- misc cost -->
{if $form.misc_cost[$sheet_n] ne '' && $form.misc_cost[$sheet_n] > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Miscellanous Cost</b></td>
	<td nowrap align=right>{$form.misc_cost[$sheet_n]}{$form.misc_cost_amount[$sheet_n]}</td>
	<td>&nbsp;</td>
</tr>
{/if}

<!-- final discount  -->
{if $form.sdiscount[$sheet_n] ne '' && $form.sdiscount[$sheet_n] > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Discount</b></td>
	<td nowrap align=right>{$form.sdiscount[$sheet_n]}
	{if strstr($form.sdiscount[$sheet_n],"%") or $form.sdiscount[$sheet_n] != $form.sdiscount_amount[$sheet_n]}
		({$total[$sheet_n].sdiscount_amount|number_format:2})
	{/if}
	</td>
	<td>&nbsp;</td>
</tr>
{/if}

<!-- "special" discount -->
{if $form.rdiscount[$sheet_n] ne '' && $form.rdiscount[$sheet_n] > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Discount from Remark#2</b></td>
	<td nowrap align=right>{$form.rdiscount[$sheet_n]}{$form.rdiscount_amount[$sheet_n]}</td>
	<td>&nbsp;</td>
</tr>
{/if}

<!-- "special" deduct cost discount -->
{if $form.ddiscount[$sheet_n] ne '' && $form.ddiscount[$sheet_n] > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Deduct Cost from Remark#2</b></td>
	<td nowrap align=right>{$form.ddiscount[$sheet_n]}{$form.ddiscount_amount[$sheet_n]}</td>
	<td>&nbsp;</td>
</tr>
{/if}



<!-- transportation cost -->
{if $form.transport_cost[$sheet_n] ne '' && $form.transport_cost[$sheet_n] > 0}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Transport Charges</b></td>
	<td nowrap align=right>{$form.transport_cost[$sheet_n]|number_format:2}{$form.transport_cost_amount[$sheet_n]}</td>
	<td>&nbsp;</td>
</tr>
{/if}

<!-- final final amount -->
<tr class="ft2 topline">
	<td colspan=9 align=right><b>Actual PO Amount</b></td>
	<td nowrap align=right>
		{$total[$sheet_n].final_amount|number_format:2}
	</td>
	{assign var=final_profit value=$total[$sheet_n].sell-$total[$sheet_n].final_amount}
	<td align=right>{$final_profit|number_format:2}</td>
</tr>
<tr class="ft2 topline">
	<td colspan=9 align=right><b>Total Selling</b></td>
	<td align=right>{$total[$sheet_n].sell|number_format:2}</td>
	<td align=right>
	{if $total[$sheet_n].sell}
	{$final_profit/$total[$sheet_n].sell*100|number_format:2}%
	{/if}
	</td>
</tr>

<!-- final final amount -->
<tr class="ft2 topline">
	<td colspan=9 align=right><b>Supplier PO Amount</b></td>
	<td nowrap align=right>
		{$total[$sheet_n].final_amount2|number_format:2}
	</td>
	<td>&nbsp;</td>
</tr>

</table>
</div>

<br><br><br>
<table width=100%>
<tr>
{if $config.po_internal_copy_3signatures}
<td valign=bottom class=small>
_________________<br>
Order By<br>
Name:
</td>

<td valign=bottom class=small>
_________________<br>
Approved By<br>
Name:
</td>

<td valign=bottom class=small>
_________________<br>
Accepted By<br>
Name:
</td>

{else}
<td width=50% valign=bottom class=small>
______________________________<br>
Accepted By<br>
Name:
</td>
{/if}

<td valign=bottom align=right nowrap>
<h1>Internal Copy</h1>
</td>
</tr>
</table>

</div>
{/if}
<!-- end loop -->
