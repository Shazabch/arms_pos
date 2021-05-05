{*
09/20/2016 09:32 Qiu Ying
- Rakanda Custom PO printing

3/21/2017 12:20 PM Andy
- Change to use data stored in database instead of recalculate everytime.
*}

{if !$skip_header}
	{include file='header.print.tpl'}

<style>
{*{if $config.po_printing_no_item_line}
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
{/if}*}

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
			<img src="data:image/gif;base64,{$encoded_comp_logo}" height=80 hspace=5 vspace=5>
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
		{if $form.offline_id}
			<tr height=22 bgcolor="#eeeeee"><td nowrap>Offline ID</td><td nowrap>#{$form.offline_id|string_format:"%05d"}</td></tr>
		{/if}
		{if $config.po_show_terms}<tr><td nowrap>Payment Terms</td><td nowrap>{$form.term|default:"-"} Days</td></tr>{/if}
	    <tr><td nowrap>Department</td><td nowrap>{$form.department}</td></tr>
		<tr><td nowrap>Ordered By</td><td nowrap>{$form.fullname}</td></tr>
		<tr><td nowrap>PO Date</td><td nowrap>{$form.po_date}
		{if $sessioninfo.u eq 'admin'}
			/{$form.po_date}
		{/if}
		</td></tr>
		
		<!--add vendor payment terms-->
		{if $form.payment_term}
		<tr>
		<td nowrap>Payment Term</td>
		<td nowrap>{$form.payment_term}</td>
		</tr>	
		{/if}
		
		<tr bgcolor="#eeeeee"><td nowrap>Delivery Date</td><td nowrap>{$form.delivery_date}</td></tr>
		<tr bgcolor="#eeeeee"><td nowrap>Cancellation Date</td><td nowrap>{$form.cancel_date}</td></tr>
		{if !$config.po_set_max_items}
		<tr bgcolor="#eeeeee"><td nowrap>Page</td><td nowrap>{$page}</td></tr>
		{/if}
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	<table cellspacing="5" cellpadding="0" border="0" width="100%">
	<tr>
		<td valign="top" width="50%" style="border:1px solid #000; padding:5px">
		<h4>Vendor</h4>
		<b>{$vendor.description}</b><br>
		{$vendor.address|nl2br}<br>
		Contact Person:{$vendor.contact_person}<br />
		Tel: {$vendor.phone_1|default:"-"}{if $vendor.phone_2} / {$vendor.phone_2}{/if}
		{if $vendor.phone_3}<br>Fax: {$vendor.phone_3}{/if}
		</td>

		<td valign="top" width="50%" style="border:1px solid #000; padding:5px">
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
<table width=100% cellspacing=0 cellpadding=0 border=0 class="box tb">
<tr class="hd topline">
	<th rowspan=2>No</th>
	<th rowspan=2 nowrap>Art/MCode<br>ARMS Code</th>
	<th width=100% rowspan=2 nowrap>SKU Description</th>
	<th rowspan=2>Cost<br>Price</th>
	<th rowspan=2>UOM</th>
	<th colspan=2>Qty</th>
	<th colspan=2>FOC</th>
	<th rowspan=2>Discount</th>
	<th rowspan=2>Nett<br>Amount</th>
	{if $form.is_under_gst}
		<th rowspan=2>GST</th>
		<th rowspan=2>Amount <br />Include GST</th>
	{/if}
</tr>
<tr class="hd topline xsmall">
	<th>Ctn</th>
	<th>Pcs</th>
	<th>Ctn</th>
	<th>Pcs</th>
</tr>
<tr>

{assign var=colspan value=12}
{assign var=remark_colspan value=2}
{assign var=famt_colspan value=10}
{if $config.link_code_name}
	{assign var=colspan value=$colspan+1}
	{assign var=remark_colspan value=$remark_colspan+1}
{/if}
{if $form.is_under_gst}
	{assign var=colspan value=$colspan+2}
	{assign var=famt_colspan value=$famt_colspan+2}
{/if}
<td colspan="{$colspan}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>

{assign var=counter value=0}
{foreach name=t from=$po_items item=item key=item_id}
{cycle name=rowbg values="#eeeeee,#dddddd" assign=rowbg}
{if $item.qty+$item.qty_loose+$item.foc+$item.foc_loose>0}
<!-- {$counter++} -->
<tr height=30 class="rw{cycle name=row values=",2"} no_border_bottom">
	{if !$page_item_info.$item_id.not_item}
		<td>{$item.item_no+1} {$count}</td>
		<td nowrap>{$item.artno_mcode|default:"&nbsp;"}
		<br>{$item.sku_item_code}</td>
	{else}
		<td>&nbsp;</td><td>&nbsp;</td>
	{/if}
	
	<td>
		<div class="crop">{$item.description}</div>
		{if $item.remark}<div class="crop"><i>{$item.remark}</i></div>{/if}
	</td>
	
	{if !$page_item_info.$item_id.not_item}
		{if $item.is_foc}
		<th>FOC</th>
		{else}
		<td nowrap align=right>{$item.order_price|number_format:$config.global_cost_decimal_points}</td>
		{/if}
		<td nowrap>{$item.order_uom|default:'EACH'}</td>
		<td nowrap>{$item.qty|qty_nf|ifzero:"&nbsp;"}</td>
		<td nowrap>{$item.qty_loose|qty_nf|ifzero:"&nbsp;"}</td>
		<td nowrap>{$item.foc|qty_nf|ifzero:"&nbsp;"}</td>
		<td nowrap>{$item.foc_loose|qty_nf|ifzero:"&nbsp;"}</td>

		{if $item.is_foc}
			<td>&nbsp;</td>
			<th>FOC</th>
			{if $form.is_under_gst}
				<td>&nbsp;</td>
				<th>FOC</th>
			{/if}
		{else}
			<td nowrap>
				{if $item.disc_remark}
					<div class="crop">
					{$item.disc_remark}<br>
					({$item.discount})
					</div>
				{elseif $item.discount}
					{$item.discount}
					{if strstr($item.discount,"%") or $form.po_option}
						<br>({$item.discount_amt|number_format:2})
					{/if}
				{else}
					&nbsp;
				{/if}
			</td>
			<td nowrap align=right>{$item.item_nett_amt|number_format:2}</td>
			{if $form.is_under_gst}
				<td nowrap align=right>{$item.item_gst_amt|number_format:2}</td>
				<td nowrap align=right>{$item.item_amt_incl_gst|number_format:2}</td>
			{/if}
		{/if}
	{else}	
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
		{/if}
	{/if}
</tr>
{/if}
{/foreach}
{repeat s=$counter+1 e=$PAGE_SIZE}
<!-- filler -->
<tr height=30 class="no_border_bottom">
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
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/repeat}

<td colspan="{$colspan}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>

<!-- total -->
<tr height=30>
    <td colspan="{$remark_colspan}" rowspan=6 style="background:none" valign=top>
    <h4>Remark</h4>
	{$form.remark|nl2br}
	</td>
	<td class="ft" nowrap>
		<b class=small>T.Ctn</b><br>
		{$total.ctn|qty_nf}
	</td>
	<td class="ft" nowrap>
		<b class=small>T.Unit</b><br>
		{$total.foc+$total.qty|qty_nf}
	</td>
	<td class="ft"  colspan=5 align=right><b>Sub Total</b></td>
	<td class="ft" nowrap align=right>{$form.subtotal_po_nett_amount|number_format:2}</td>
	{if $form.is_under_gst}
		<td class="ft" nowrap align=right>{$form.subtotal_po_gst_amount|number_format:2}</td>
		<td class="ft" nowrap align=right>{$form.subtotal_po_amount_incl_gst|number_format:2}</td>
	{/if}
</tr>

<!-- misc cost -->
{if $form.misc_cost ne '' && $form.misc_cost > 0}
<tr height=20 class="ft topline">
	<td colspan="7" align=right><b>Miscellanous Cost</b></td>
	<td nowrap align="right">
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
<tr height=20 class="ft topline">
	<td colspan="7" align=right><b>Discount</b></td>
	<td nowrap align="right">{$form.sdiscount}
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

<!-- "special" discount
{if $form.rdiscount}
<tr height=20 class="ft topline">
	<td colspan=9 align=right><b>Discount from Remark</b></td>
	<td nowrap align=right>{$form.rdiscount}{$form.rdiscount_amount}</td>
</tr>
{/if}
 -->


<!-- transportation cost -->
{if $form.transport_cost ne '' && $form.transport_cost > 0}
<tr height=20 class="ft topline">
	<td colspan="7" align=right><b>Transport Charges</b></td>
	<td nowrap {if $form.is_under_gst}colspan="3"{/if} align="right">
		{if $form.transport_cost != $form.transport_cost_amt}
			{$form.transport_cost}({$form.transport_cost_amt|number_format:2})
		{else}
			{$form.transport_cost_amt|number_format:2}
		{/if}
	</td>
</tr>
{/if}

<!-- final final amount -->
<tr class="ft2 topline">
	<td colspan="{$famt_colspan}" align=right>
		<h1>This PO Total:
		RM{$form.supplier_po_amt|number_format:2}
		</h1>
	</td>
</tr>

{if $form.is_under_gst}
	<!-- final GST amount -->
	<tr class="ft2 topline">
		<td colspan="{$famt_colspan}" align=right>
			<h1>This PO Total Include GST:
			RM{$form.supplier_po_amt_incl_gst|number_format:2}
			</h1>
		</td>
	</tr>
{/if}

</table>
</div>
<br><br><br>

<table width="100%" style="font-size:12px">
<tr>
<td width="30%" valign="bottom" >
PO Approved by: {$form.last_approval_user.fullname}<br>
Position: {$form.last_approval_user.position}<br>
<span style="font-size:9px">This is a computer generated document, no signature is required.</span>
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
</table>
</div>
{/if}

<!---------------------------------------------  BRANCH COPY -------------------------------------------------->
{if $print.branch_copy}
{assign var=colspan value=14}
{assign var=remark_colspan value=3}
{if $config.link_code_name}
	{assign var=colspan value=$colspan+1}
	{assign var=remark_colspan value=$remark_colspan+1}
{/if}
{if $form.is_under_gst}
	{assign var=colspan value=$colspan+2}
{/if}
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td valign=bottom>
		<h2>{$billto.description}</h2>

		<table cellspacing=5 cellpadding=0 border=0 width=100%>
		<tr>
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<h4>Vendor</h4>
			<b>{$vendor.description}</b><br>
			{$vendor.address|nl2br}<br>
			Contact Person:{$vendor.contact_person}<br />
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
		{if $form.offline_id}
			<tr height=22 bgcolor="#eeeeee"><td nowrap>Offline ID</td><td nowrap>#{$form.offline_id|string_format:"%05d"}</td></tr>
		{/if}
		{if $config.po_show_terms}<tr><td nowrap>Payment Terms</td><td nowrap>{$form.term|default:"-"} Days</td></tr>{/if}
	    <tr><td nowrap>Department</td><td nowrap>{$form.department}</td></tr>
		<tr><td nowrap>Ordered By</td><td nowrap>{$form.fullname}</td></tr>
		<tr><td nowrap>PO Date</td><td nowrap>{$form.po_date}</td></tr>
		
		<!--add vendor payment terms-->
		{if $form.payment_term}
		<tr>
		<td nowrap>Payment Term</td>
		<td nowrap>{$form.payment_term}</td>
		</tr>	
		{/if}
		
		<tr bgcolor="#cccccc"><td nowrap>Delivery Date</td><td nowrap>{$form.delivery_date}</td></tr>
		<tr bgcolor="#cccccc"><td nowrap>Cancellation Date</td><td nowrap>{$form.cancel_date}</td></tr>
		{if !$config.po_set_max_items}
		<tr bgcolor="#cccccc"><td nowrap>Page</td><td nowrap>{$page}</td></tr>
		{/if}
	  	</table>
	</td>
</tr>
</table>

<!--- item table -->
<div style="border:2px solid #000; padding:1px;">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="box tb">
<tr class="hd topline">
	<th rowspan=2>No</th>
	<th rowspan=2 nowrap>Art/MCode<br>ARMS Code</th>
	<th width=100% rowspan=2 nowrap>SKU Description</th>
	<th {if !$form.is_under_gst || !$sp_inclusive_tax}rowspan=2{/if}>S.S.P</th>
	<th rowspan=2>Cost</th>
	<th rowspan=2>Purchase<br />UOM</th>
	<th colspan=2>Qty</th>
	<th colspan=2 style="background:#aaa">FOC</th>
	<th rowspan=2>Discount</th>
	<th nowrap>T.Selling</th>
	<th nowrap style="background:#aaa">GP</th>
	{if $form.is_under_gst}
		<th rowspan=2>GST</th>
		<th rowspan=2>Amount <br />Include GST</th>
	{/if}
</tr>
<tr class="hd topline">
	{if $form.is_under_gst && $sp_inclusive_tax}
		<th>N.S.P</th>
	{/if}
	<th class="xsmall">Ctn</th>
	<th class="xsmall">Pcs</th>
	<th class="xsmall" style="background:#aaa">Ctn</th>
	<th class="xsmall" style="background:#aaa">Pcs</th>
	<th nowrap>Nett Amt</th>
	<th nowrap style="background:#aaa">Profit(%)</th>
</tr>
<tr>
<td colspan="{$colspan}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>
{assign var=counter value=0}
{foreach name=t from=$po_items item=item key=item_id}
{cycle name=rowbg values="#ddd,#ccc" assign=rowbg}
{if $item.qty+$item.qty_loose+$item.foc+$item.foc_loose>0}
<!-- {$counter++} -->
<tr height=15 class="rw{cycle name=crow values=",2"} no_border_bottom">
	{if !$page_item_info.$item_id.not_item}
		<td rowspan=2>{$item.item_no+1}</td>
		<td rowspan=2 nowrap>{$item.artno_mcode|default:"&nbsp;"}
		<br>{$item.sku_item_code}</td>
	{else}
		<td rowspan=2>&nbsp;</td><td rowspan=2>&nbsp;</td>
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
		<td {if !$form.is_under_gst || !$sp_inclusive_tax}rowspan=2{/if} style="border-top:1px solid #000;{if $sell<$cost}font-weight:bold;{/if}" align="right" nowrap>
			{if $form.is_under_gst && $item.inclusive_tax eq 'yes'}
				{$item.gst_selling_price|number_format:2}
			{else}
				{$item.selling_price|number_format:2}
			{/if}
		</td>

		{if $item.is_foc}
			<th rowspan=2 style="border-top:1px solid #000;">FOC</th>
		{else}
			<td rowspan=2 style="border-top:1px solid #000;" nowrap align=right>
				{$item.order_price|number_format:$config.global_cost_decimal_points}
			</td>
		{/if}
		<td rowspan=2 align="center" nowrap>{$item.order_uom|default:'EACH'}</td>
		<td rowspan=2 nowrap>{$item.qty|qty_nf|ifzero:"&nbsp;"}</td>
		<td rowspan=2 nowrap>{$item.qty_loose|qty_nf|ifzero:"&nbsp;"}</td>
		<td rowspan=2 nowrap style="background:{$rowbg}">{$item.foc|qty_nf|ifzero:"&nbsp;"}</td>
		<td rowspan=2 nowrap style="background:{$rowbg}">{$item.foc_loose|qty_nf|ifzero:"&nbsp;"}</td>

		{if $item.is_foc}
			<td rowspan=2>&nbsp;</td>
		{else}
			<td rowspan=2 nowrap>
				{if $item.disc_remark}
					<div class="crop">
					{$item.disc_remark}<br>
					({$item.discount})
					</div>
				{elseif $item.discount}
					{$item.discount}
					{if strstr($item.discount,"%") or $form.po_option}
						<br>({$item.discount_amt|number_format:2})
					{/if}
				{else}
				&nbsp;
				{/if}
			</td>
		{/if}
		<td nowrap align=right style="border-top:1px solid #000">{$item.item_total_selling|number_format:2}</td>
		{if $item.is_foc}
			{assign var=total_profit value=$item.item_total_selling}
		{else}
			{assign var=total_profit value=$item.item_total_selling-$item.item_nett_amt}
		{/if}
		<td nowrap align=right style="background:{$rowbg}; border-top:1px solid #000">{$total_profit|number_format:2}</td>
		{if $form.is_under_gst}
			{if $item.is_foc}
				<td rowspan=2>&nbsp;</td>
				<th rowspan=2>FOC</th>
			{else}
				<td rowspan=2 nowrap align=right>{$item.item_gst_amt|number_format:2}</td>
				<td rowspan=2 nowrap align=right>{$item.item_amt_incl_gst|number_format:2}</td>
			{/if}
		{/if}
	{else}
		<td {if !$form.is_under_gst || !$sp_inclusive_tax}rowspan=2{/if} style="border-bottom:0px">&nbsp;</td>
		<td rowspan=2 >&nbsp;</td>
		<td rowspan=2>&nbsp;</td>
		<td rowspan=2>&nbsp;</td>
		<td rowspan=2>&nbsp;</td>
		<td rowspan=2 style="background:{$rowbg}">&nbsp;</td>
		<td rowspan=2 style="background:{$rowbg}">&nbsp;</td>
		<td rowspan=2>&nbsp;</td>
		<td>&nbsp;</td>
		<td style="background:{$rowbg}">&nbsp;</td>
		{if $form.is_under_gst}
			<td rowspan=2>&nbsp;</td>
			<td rowspan=2>&nbsp;</td>
		{/if}
	{/if}
</tr>
<tr height=15 class="rw{cycle name=crow2 values=',2'}">
	{if !$page_item_info.$item_id.not_item}
		{if $form.is_under_gst  && $sp_inclusive_tax}
			<td style="border-top:1px solid #000;" nowrap align=right>
				{if $item.inclusive_tax eq 'yes'}
					{$item.selling_price|number_format:2}
				{else}
					-
				{/if}
			</td>
		{/if}
		{if $item.is_foc}
		<th style="border-top:1px solid #000">FOC</th>
		{else}
		<td style="border-top:1px solid #000" nowrap align=right>{$item.item_nett_amt|number_format:2}</td>
		{/if}
		
		{assign var=gp value=$total_profit/$item.item_total_selling*100|number_format:2}
		<td style="background:{$rowbg};border-top:1px solid #000;{if $gp<0}font-weight:bold;{/if}" nowrap align=right>{if $item.item_total_selling<=0}-{else}{$gp}%{/if}</td>
	{else}
		{if $form.is_under_gst  && $sp_inclusive_tax}
			<td style="border-bottom:0px">&nbsp;</td>
		{/if}
		<td style="border-bottom:0px;border-top:0px">&nbsp;</td><td style="background:{$rowbg};border-bottom:0px;{if $gp<0}font-weight:bold;{/if}">&nbsp;</td>
	{/if}
</tr>
{/if}
{/foreach}

{repeat s=$counter+1 e=$PAGE_SIZE}
<!-- filler -->
<tr height=30 class="no_border_bottom">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td style="background:{$rowbg}">&nbsp;</td>
	<td style="background:{$rowbg}">&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td style="background:{$rowbg}">&nbsp;</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/repeat}

<td colspan="{$colspan}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>

<!-- total -->
<tr height=30>
    <td colspan="{$remark_colspan}" rowspan="10" style="background:none" valign="top">
    <h4>Remark</h4>
	{$form.remark|nl2br}<br>
	<br>
    <h4>Remark#2 (For Internal Use)</h4>
	{$form.remark2|nl2br}
	</td>
	<td rowspan=2 class="ft" nowrap>
		<b class=small>T.Ctn</b><br>
		{$total.ctn|qty_nf}
	</td>
	<td rowspan=2 class="ft" nowrap>
		<b class=small>T.Unit</b><br>
		{$total.foc+$total.qty|qty_nf}
	</td>
	<td colspan=5 class="ft" align=right><b>T.Amount</b></td>
	{assign var=total_profit value=$form.total_selling_amt-$form.subtotal_po_nett_amount}
	<td class="ft" nowrap align=right>{$form.subtotal_po_nett_amount|number_format:2}</td>
	<td class="ft" nowrap align=right>{$total_profit|number_format:2}</td>
	{if $form.is_under_gst}
		{*assign var=total_profit value=$total.sell-$total.gst_amount*}
		<td class="ft" nowrap align=right>{$form.subtotal_po_gst_amount|number_format:2}</td>
		<td class="ft" nowrap align=right>{$form.subtotal_po_amount_incl_gst|number_format:2}</td>
	{/if}
</tr>

<tr class="ft topline">
	<td align=right colspan=5><b>T.Selling</b></td>
	<td align=right>{$form.total_selling_amt|number_format:2}</td>
	<td align=right>{if $form.total_selling_amt<=0}-{else}{$total_profit/$form.total_selling_amt*100|number_format:2}%{/if}</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>

<!-- misc cost -->
{if $form.misc_cost ne '' && $form.misc_cost > 0}
<tr height=20 class="ft topline">
	<td colspan="7" align=right><b>Miscellanous Cost</b></td>
	<td nowrap align=right>
		{$form.misc_cost}
		{if strstr($form.misc_cost,"%") or $form.misc_cost != $form.misc_cost_amt}
			({$form.misc_cost_amt|number_format:2})
		{/if}
	</td>
	<td>&nbsp;</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>{$form.gst_misc_cost_amt|number_format:2}</td>
	{/if}
</tr>
{/if}

<!-- final discount  -->
{if $form.sdiscount ne '' && $form.sdiscount > 0}
<tr height=20 class="ft topline">
	<td colspan="7" align=right><b>Discount</b></td>
	<td nowrap align=right>{$form.sdiscount}
		{if strstr($form.sdiscount,"%") or $form.sdiscount != $form.sdiscount_amt}
			({$form.sdiscount_amt|number_format:2})
		{/if}
	</td>
	<td>&nbsp;</td>
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
	<td colspan="7" align=right><b>Discount from Remark#2</b></td>
	<td nowrap align=right>{$form.rdiscount}
		{if strstr($form.rdiscount,"%") or $form.rdiscount != $form.rdiscount_amt}
			({$form.rdiscount_amt|number_format:2})
		{/if}
	</td>
	<td>&nbsp;</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>{$form.gst_rdiscount_amt|number_format:2}</td>
	{/if}
</tr>
{/if}

<!-- "special" deduct cost discount -->
{if $form.ddiscount ne '' && $form.ddiscount > 0}
<tr height=20 class="ft topline">
	<td colspan="7" align=right><b>Deduct Cost from Remark#2</b></td>
	<td nowrap align=right>{$form.ddiscount}
		{if strstr($form.ddiscount,"%") or $form.ddiscount != $form.ddiscount_amt}
			({$form.ddiscount_amt|number_format:2})
		{/if}
	</td>
	<td>&nbsp;</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>{$form.gst_ddiscount_amt|number_format:2}</td>
	{/if}
</tr>
{/if}



<!-- transportation cost -->
{if $form.transport_cost ne '' && $form.transport_cost > 0}
<tr height=20 class="ft topline">
	<td colspan="7" align=right><b>Transport Charges</b></td>
	<td nowrap align=right>
		{if $form.transport_cost != $form.transport_cost_amt}
			{$form.transport_cost}({$form.transport_cost_amt|number_format:2})
		{else}
			{$form.transport_cost_amt|number_format:2}
		{/if}
	</td>
	<td>&nbsp;</td>
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
	<td colspan=7 align=right><b>Actual PO Amount</b></td>
	<td nowrap align=right>
		{$form.po_amount|number_format:2}
	</td>
	{assign var=final_profit value=$form.total_selling_amt-$form.po_amount}
	<td align=right>{$final_profit|number_format:2}</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>{$form.po_amount_incl_gst|number_format:2}</td>
	{/if}
</tr>
<tr class="ft2 topline">
	<td colspan=7 align=right><b>Total Selling</b></td>
	<td align=right>{$form.total_selling_amt|number_format:2}</td>
	<td align=right>
	{if $form.total_selling_amt}
	{$final_profit/$form.total_selling_amt*100|number_format:2}%
	{/if}
	</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>

<!-- final final amount -->
<tr class="ft2 topline">
	<td colspan=7 align=right><b>Supplier PO Amount</b></td>
	<td nowrap align=right>
		{$form.supplier_po_amt|number_format:2}
	</td>
	<td>&nbsp;</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td nowrap align=right>{$form.supplier_po_amt_incl_gst|number_format:2}</td>
	{/if}
</tr>
</table>
</div>

<br><br><br>
<table width="100%" style="font-size:12px">
<tr>
<td width="50%" valign="bottom" >
PO Approved by: {$form.last_approval_user.fullname}<br>
Position: {$form.last_approval_user.position}<br>
<span style="font-size:9px">This is a computer generated document, no signature is required.</span>
</td>

<td valign=bottom align=right nowrap>
<h1>Internal Copy</h1>
</td>
</tr>
</table>

</div>
{/if}
<!-- end loop -->
