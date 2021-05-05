{*
REVISION HISTORY
=================
7/15/2011 1:52:28 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

9/23/2011 10:55:45 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

4/25/2012 10:38:12 AM Justin
- Fixed bug of table that did not setup the width and causing without any width during send by email.
- Added to show encoded image if send by vendor.

7/10/2012 2:42 PM Andy
- Remove deliver and cancellation date.
- Remove deliver date for vendor copy.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

3/10/2015 12:46 PM Justin
- Enhanced to have GST information.

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

3/21/2017 12:20 PM Andy
- Change to use data stored in database instead of recalculate everytime.
*}

{config_load file="site.conf"}
{if !$skip_header}
{include file='header.print.tpl'}
<style>
{if $config.po_printing_no_item_line}
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
td div.crop{
  height:auto;
  max-height:2em;
  overflow:hidden;
}
{/literal}
</style>

<script type="text/javascript">
var printName = '{$form.po_no}';

{literal}
function start_print(){
	document.title=printName;
	if(typeof(jsPrint) == 'undefined'){
		window.print();
	}else{
		jsPrint({'printName': printName});
	}
}
{/literal}
</script>

<body onload="start_print();">
{/if}
<!-- loop for each sheets -->
{if $print.vendor_copy}
<div class=printarea>

<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td>
		{if $encoded_comp_logo}
			<img src="data:image/png;base64,{$encoded_comp_logo}" height=80 hspace=5 vspace=5>
		{else}
			<img src="{get_logo_url mod='po'}" height=80 hspace=5 vspace=5>
		{/if}
	</td>
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
		<tr bgcolor="#eeeeee"><td nowrap>Ordered By</td><td nowrap>{$form.fullname}</td></tr>
		<tr><td nowrap>PO Date</td><td nowrap>{$form.po_date|date_format:$config.dat_format}</td></tr>
		
		<!--add vendor payment terms-->
		{if $form.payment_term}
		<tr>
		<td nowrap>Payment Term</td>
		<td nowrap>{$form.payment_term}</td>
		</tr>	
		{/if}
		
		{if !$config.po_set_max_items}
		<tr bgcolor="#eeeeee"><td nowrap>Page</td><td nowrap>{$page}</td></tr>
		{/if}
	  	</table>
	</td>
</tr>
<tr>
<td colspan="2">
	<table cellspacing="5" cellpadding="0" border="0" width="100%">
	<tr>
		<td valign="top" colspan="2" style="border:1px solid #000; padding:5px">
		<h4>Vendor: {$vendor.description}</h4>
		{$vendor.address|nl2br}<br>
		Tel: {$vendor.phone_1|default:"-"}{if $vendor.phone_2} / {$vendor.phone_2}{/if}
		{if $vendor.phone_3}<br>Fax: {$vendor.phone_3}{/if}
		</td>
	</tr>
	</table>
</td>
</tr>
</table>

<!--- item table -->
<div style="border:2px solid #000; padding:1px;">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="box small tb">
<tr class="hd topline">
	<th>No</th>
	<th>Photo</th>
	<th nowrap>Art/MCode<br>ARMS Code</th>
	<th width="100%" nowrap>SKU Description</th>
	<th>Cost<br>Price</th>
	<th>Qty</th>
	<th>Amount(RM)</th>
	{if $form.is_under_gst}
		<th>GST</th>
		<th>Amount <br />Include GST</th>
	{/if}
</tr>
<tr>
</tr>

{assign var=counter value=0}
{foreach name=t from=$po_items item=item key=item_id}
{cycle name=rowbg values="#eeeeee,#dddddd" assign=rowbg}
{if $item.qty+$item.qty_loose+$item.foc+$item.foc_loose>0}
<!-- {$counter++} -->
<tr height="110" class="rw{cycle name=row values=",2"} no_border_bottom">
	{if !$page_item_info.$item_id.not_item}
		<td>{$item.item_no+1}</td>
		<td align="center">&nbsp;
			{if $config.po_show_photo}
				{if $item.photo}
					{assign var=p value=$item.photo}
					<img src="{$p}" width="100" height="100" />	
				{else}
					- No Photo -
				{/if}
			{/if}
		</td>
		<td nowrap>{$item.artno_mcode|default:"&nbsp;"}
		<br>{$item.sku_item_code}</td>
	{else}
		<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	{/if}
	
	<td>
		<div class="crop">{$item.description}</div>
		{if $item.remark}<div class="crop"><i>{$item.remark}</i></div>{/if}
	</td>
	
	{if !$page_item_info.$item_id.not_item}
		<td nowrap align="right">{$item.order_price|number_format:$config.global_cost_decimal_points}</td>
		<td nowrap>{$item.qty_loose|qty_nf|ifzero:"&nbsp;"}</td>
		<td nowrap align="right">{$item.item_nett_amt|number_format:2}</td>
		{if $form.is_under_gst}
			<td nowrap align=right>{$item.item_gst_amt|number_format:2}</td>
			<td nowrap align=right>{$item.item_amt_incl_gst|number_format:2}</td>
		{/if}
	{else}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if $form.is_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	{/if}
</tr>
{/if}
{/foreach}
{repeat s=$counter+1 e=$PAGE_SIZE}
<!-- filler -->
<tr height="110" class="no_border_bottom">
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
	{/if}
</tr>
{/repeat}

<!-- total -->
<tr height="30" class="total_row">
    <td colspan="4" rowspan="6" style="background:none" valign="top">
	    <h4>Remark</h4>
		{$form.remark|nl2br}
	</td>
	<td class="ft">&nbsp;</td>
	<td class="ft" nowrap>
		<b class=small>T.Unit</b><br>
		{$total.foc+$total.qty|qty_nf}
	</td>
	<td class="ft" nowrap align=right>{$form.subtotal_po_nett_amount|number_format:2}</td>
	{if $form.is_under_gst}
		<td class="ft" nowrap align=right>{$form.subtotal_po_gst_amount|number_format:2}</td>
		<td class="ft" nowrap align=right>{$form.subtotal_po_amount_incl_gst|number_format:2}</td>
	{/if}
</tr>

<!-- misc cost -->
{if $form.misc_cost ne '' && $form.misc_cost > 0}
<tr height="20" class="ft topline">
	<td colspan="2" align=right><b>Miscellanous Cost</b></td>
	<td nowrap align=right>
		{$form.misc_cost}
		{if strstr($form.misc_cost,"%") or $form.misc_cost != $form.misc_cost_amt}
			({$form.misc_cost_amt|number_format:2})
		{/if}
	</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align="right">{$form.gst_misc_cost_amt|number_format:2}</td>
	{/if}
</tr>
{/if}

<!-- final discount  -->
{if $form.sdiscount ne '' && $form.sdiscount > 0}
<tr height="20" class="ft topline">
	<td colspan="2" align=right><b>Discount</b></td>
	<td nowrap align=right>{$form.sdiscount}
		{if strstr($form.sdiscount,"%") or $form.sdiscount != $form.sdiscount_amt}
			({$form.sdiscount_amt|number_format:2})
		{/if}
	</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align="right">
			{$form.gst_sdiscount_amt|number_format:2}
		</td>
	{/if}
</tr>
{/if}

<!-- transportation cost -->
{if $form.transport_cost ne '' && $form.transport_cost > 0}
<tr height=20 class="ft topline">
	<td colspan="2" align=right><b>Transport Charges</b></td>
	<td nowrap {if $form.is_under_gst}colspan="3"{/if} align=right>
		{if $form.transport_cost != $form.transport_cost_amt}
			{$form.transport_cost}({$form.transport_cost_amt|number_format:2})
		{else}
			{$form.transport_cost_amt|number_format:2}
		{/if}
	</td>
</tr>
{/if}

{assign var=famt_colspan value=3}
{if $form.is_under_gst}
	{assign var=famt_colspan value=$famt_colspan+2}
{/if}

<!-- final final amount -->
<tr class="ft2 topline">
	<td colspan="{$famt_colspan}" align="right">
		<h1>This PO Total:
		RM{$form.supplier_po_amt|number_format:2}
		</h1>
	</td>
</tr>

{if $form.is_under_gst}
	<!-- final GST amount -->
	<tr class="ft2 topline">
		<td colspan="{$famt_colspan}" align="right">
			<h1>This PO Total Include GST:
			RM{$form.supplier_po_amt_incl_gst|number_format:2}
			</h1>
		</td>
	</tr>
{/if}

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
			<h4>Vendor: {$vendor.description}</h4>
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
		<tr bgcolor="#cccccc"><td nowrap>Ordered By</td><td nowrap>{$form.fullname}</td></tr>
		<tr><td nowrap>PO Date</td><td nowrap>{$form.po_date|date_format:$config.dat_format}</td></tr>
		
		<!--add vendor payment terms-->
		{if $form.payment_term}
		<tr>
		<td nowrap>Payment Term</td>
		<td nowrap>{$form.payment_term}</td>
		</tr>	
		{/if}

		{if !$config.po_set_max_items}
		<tr bgcolor="#cccccc"><td nowrap>Page</td><td nowrap>{$page}</td></tr>
		{/if}
	  	</table>
	</td>
</tr>
</table>

<!--- item table -->
<div style="border:2px solid #000; padding:1px;">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="box small tb">
<tr class="hd topline">
	<th rowspan=2>No</th>
	<th rowspan="2">Photo</th>
	<th rowspan=2 nowrap>Art/MCode<br>ARMS Code</th>
	<th width=100% rowspan=2 nowrap>SKU Description</th>
	<th style="background:#aaa">Selling</th>
	<th rowspan=2>Qty</th>
	<th nowrap>T.Selling</th>
	{if $form.is_under_gst}
		<th rowspan=2>GST</th>
		<th rowspan=2>Amount <br />Include GST</th>
	{/if}
</tr>
<tr class="hd topline">
	<th style="background:#aaa">Cost</th>
	<th nowrap>Nett Amt</th>
</tr>
<tr>

</tr>
{assign var=counter value=0}
{foreach name=t from=$po_items item=item key=item_id}
{cycle name=rowbg values="#ddd,#ccc" assign=rowbg}
{if $item.qty+$item.qty_loose+$item.foc+$item.foc_loose>0}
<!-- {$counter++} -->
<tr height="110" class="rw{cycle name=crow values=",2"} no_border_bottom">
	{if !$page_item_info.$item_id.not_item}
		<td rowspan=2>{$item.item_no+1}</td>
		<td rowspan="2" align="center">&nbsp;
			{if $config.po_show_photo}
				{if $item.photo}
					{assign var=p value=$item.photo}
					<img src="{$p}" width="100" height="100" />	
				{else}
					- No Photo -
				{/if}
			{/if}
		</td>
		<td rowspan=2 nowrap>{$item.artno_mcode|default:"&nbsp;"}
		<br>{$item.sku_item_code}</td>
	{else}
		<td rowspan=2>&nbsp;</td><td rowspan=2>&nbsp;</td><td rowspan=2>&nbsp;</td>
	{/if}
	
	<td rowspan=2>
	    <div class="crop">
    		{if $item.is_foc}({$item.foc_id}){/if}
    		{$item.description}
    		{assign var=foc_id value=$item.id}
    		{if $foc_annotations.$foc_id}({$foc_annotations.$foc_id}){/if}
		  </div>
		{if $item.remark}<div class="crop"><i>{$item.remark}</i></div>{/if}
		{if $item.remark2}<div class="crop"><i>{$item.remark2}</i></div>{/if}
	</td>
	
	{if !$page_item_info.$item_id.not_item}
		{assign var=cost value=$item.order_price/$item.order_uom_fraction}
		{assign var=sell value=$item.selling_price/$item.selling_uom_fraction}
		<td nowrap align=right style="background:{$rowbg};{if $sell<$cost}font-weight:bold;{/if}">
		{$item.selling_price|number_format:3}
		</td>
		<td rowspan=2 nowrap>{$item.qty_loose|qty_nf|ifzero:"&nbsp;"}</td>

		<td nowrap align=right>{$item.item_total_selling|number_format:2}</td>
		{if $form.is_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	{else}
		<td style="background:{$rowbg};{if $sell<$cost}font-weight:bold;{/if}">&nbsp;</td>
		<td rowspan=2>&nbsp;</td>
		<td>&nbsp;</td>
		{if $form.is_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	{/if}
</tr>
<tr height=15 class="rw{cycle name=crow2 values=",2"}">
	{if !$page_item_info.$item_id.not_item}
		<td style="background:{$rowbg};border-top:1px solid #000" nowrap align=right>
			{$item.order_price|number_format:3}
		</td>
		<td style="border-top:1px solid #000" nowrap align=right>{$item.item_nett_amt|number_format:2}</td>
		{if $form.is_under_gst}
			{if $item.is_foc}
				<td style="border-top:1px solid #000">&nbsp;</td>
				<td style="border-top:1px solid #000">&nbsp;</td>
			{else}
				<td style="border-top:1px solid #000" nowrap align=right>{$item.item_gst_amt|number_format:2}</td>
				<td style="border-top:1px solid #000" nowrap align=right>{$item.item_amt_incl_gst|number_format:2}</td>
			{/if}
		{/if}
	{else}
		<td  style="background:{$rowbg};border-top:0px;border-bottom:0px">&nbsp;</td>
		<td style="border-top:0px;border-bottom:0px">&nbsp;</td>
		{if $form.is_under_gst}
			<td style="border-top:0px;border-bottom:0px">&nbsp;</td><td style="border-top:0px;border-bottom:0px">&nbsp;</td>
		{/if}
	{/if}
</tr>
{/if}
{/foreach}

{repeat s=$counter+1 e=$PAGE_SIZE}
<!-- filler -->
<tr height="110" class="no_border_bottom">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td style="background:{$rowbg}">&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/repeat}

<!-- total -->
<tr height="30" class="total_row">
    <td colspan="4" rowspan="10" style="background:none" valign="top">
	    <h4>Remark</h4>
		{$form.remark|nl2br}<br>
		<br>
	    <h4>Remark#2 (For Internal Use)</h4>
		{$form.remark2|nl2br}
	</td>
	<td rowspan=2 class="ft" nowrap>
		&nbsp;
	</td>
	<td rowspan=2 class="ft" nowrap>
		<b class=small>T.Unit</b><br>
		{$total.foc+$total.qty|qty_nf}
	</td>
	<td align="right" class="ft">{$form.total_selling_amt|number_format:2}</td>
	{if $form.is_under_gst}
		<td class="ft">&nbsp;</td>
		<td class="ft">&nbsp;</td>
	{/if}
</tr>

<tr class="ft topline">
	<td class="ft" nowrap align=right>{$form.subtotal_po_nett_amount|number_format:2}</td>
	{if $form.is_under_gst}
		{assign var=total_profit value=$form.total_selling_amt-$form.subtotal_po_nett_amount}
		<td class="ft" nowrap align=right>{$form.subtotal_po_gst_amount|number_format:2}</td>
		<td class="ft" nowrap align=right>{$form.subtotal_po_amount_incl_gst|number_format:2}</td>
	{/if}
</tr>

<!-- misc cost -->
{if $form.misc_cost ne '' && $form.misc_cost > 0}
<tr height=20 class="ft topline">
	<td colspan="2" align=right><b>Miscellanous Cost</b></td>
	<td nowrap align=right>
		{$form.misc_cost}
		{if strstr($form.misc_cost,"%") or $form.misc_cost != $form.misc_cost_amt}
			({$form.misc_cost_amt|number_format:2})
		{/if}
	</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>{$form.gst_misc_cost_amt|number_format:2}</td>
	{/if}
</tr>
{/if}

<!-- final discount  -->
{if $form.sdiscount ne '' && $form.sdiscount > 0}
<tr height=20 class="ft topline">
	<td colspan="2" align=right><b>Discount</b></td>
	<td nowrap align=right>{$form.sdiscount}
		{if strstr($form.sdiscount,"%") or $form.sdiscount != $form.sdiscount_amt}
			({$form.sdiscount_amt|number_format:2})
		{/if}
	</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>
			{$form.gst_sdiscount_amt|number_format:2}
		</td>
	{/if}
</tr>
{/if}

<!-- "special" discount -->
{if $form.rdiscount ne '' && $form.rdiscount > 0}
<tr height=20 class="ft topline">
	<td colspan="2" align=right><b>Discount from Remark#2</b></td>
	<td nowrap align=right>{$form.rdiscount}
		{if strstr($form.rdiscount,"%") or $form.rdiscount != $form.rdiscount_amt}
			({$form.rdiscount_amt|number_format:2})
		{/if}
	</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>{$form.gst_rdiscount_amt|number_format:2}</td>
	{/if}
</tr>
{/if}

<!-- "special" deduct cost discount -->
{if $form.ddiscount ne '' && $form.ddiscount > 0}
<tr height=20 class="ft topline">
	<td colspan="2" align=right><b>Deduct Cost from Remark#2</b></td>
	<td nowrap align=right>{$form.ddiscount}
		{if strstr($form.ddiscount,"%") or $form.ddiscount != $form.ddiscount_amt}
			({$form.ddiscount_amt|number_format:2})
		{/if}
	</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>{$form.gst_ddiscount_amt|number_format:2}</td>
	{/if}
</tr>
{/if}



<!-- transportation cost -->
{if $form.transport_cost ne '' && $form.transport_cost > 0}
<tr height=20 class="ft topline">
	<td colspan="2" align=right><b>Transport Charges</b></td>
	<td nowrap align=right>
		{if $form.transport_cost != $form.transport_cost_amt}
			{$form.transport_cost}({$form.transport_cost_amt|number_format:2})
		{else}
			{$form.transport_cost_amt|number_format:2}
		{/if}
	</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>
			{if $form.transport_cost != $form.transport_cost_amt}
				{$form.transport_cost}({$form.transport_cost_amt|number_format:2})
			{else}
				{$form.transport_cost_amt|number_format:2}
			{/if}
		</td>
	{/if}
</tr>
{/if}

<!-- final final amount -->
<tr class="ft2 topline">
	<td colspan="2" align=right><b>Actual PO Amount</b></td>
	<td nowrap align=right>
		{$form.po_amount|number_format:2}
	</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>{$form.po_amount_incl_gst|number_format:2}</td>
	{/if}
	{assign var=final_profit value=$form.total_selling_amt-$form.po_amount}
</tr>
<tr class="ft2 topline">
	<td colspan="2" align=right><b>Total Selling</b></td>
	<td align=right>{$form.total_selling_amt|number_format:2}</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>

<!-- final final amount -->
<tr class="ft2 topline">
	<td colspan="2" align=right><b>Supplier PO Amount</b></td>
	<td nowrap align=right>
		{$form.supplier_po_amt|number_format:2}
	</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>{$form.supplier_po_amt_incl_gst|number_format:2}</td>
	{/if}
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
