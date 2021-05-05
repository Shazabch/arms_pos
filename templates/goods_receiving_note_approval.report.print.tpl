{*
Revision history
================
19.03.07 yinsee
- added 'rounding_amt' column (for rounding error correction)

9/20/2007 4:51:59 PM gary
-alter position of ARMS code, artno/mcode.

9/23/2010 5:23:36 PM Justin
- Added strikes for all the reconcile items.

10/26/2010 4:23:21 PM Justin
- Fixed the missing border.

7/15/2011 1:44:47 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

10/5/2011 7:12:32 PM Justin
- Added to show document no from GRR under Invoice/DO No while is using GRN Future and document type is not PO.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

12/11/2013 2:13 PM Justin
- Enhanced to show offline ID.

11/18/2014 5:26 PM Justin
- Enhanced to show GST column and calculation.

4/7/2015 2:52 PM Justin
- Enhanced to add missing GST info.

4/8/2015 11:19 AM Justin
- Enhanced to show returned items on new page.
- Enhanced to show returned items only if user tick to print.

10/28/2015 1:47 PM DingRen
- Enhance GRN Amount (After Adjust) to GRN Amount include GST (After Adjust) if the GRN is under GST
- Final Adjustment Amount = INV/DO Amount (-) D/N Amount (+/-) Adjustment Amount

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

03/01/2016 15:58 Edwin
- Enhanced on show GST amount when GRN is under gst

3/23/2017 10:17 AM Andy
- Fix displayed wrong PO Discount Amount in Remarks.

4/25/2017 2:48 PM Khausalya
- Enhanced changes from RM to use config setting. 

8/8/2018 4:27 PM Justin
- Bug fixed on printing this report will cause other report which print at the same time cannot read css attribute.

8/28/2018 11:31 AM Justin
- Bug fixed on GST amount is wrong.

9/24/2018 5:45 PM Justin
- Enhanced to always show D/N amount from GRN Summary (Account).

10/30/2018 9:45 AM Justin
- Enhanced to show Branch Company Registration No. after company name.   

5/22/2019 3:22 PM William
- Enhance "GRN","GRR" word to use report_prefix.
*}
{if !$skip_header}
{include file='header.print.tpl'}

<style>
{if $config.grn_printing_no_item_line}
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
</style>

<script type="text/javascript">
var doc_no = '{$branch.report_prefix}{$grn.id|string_format:"%05d"}';
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
<div style="float:right;text-align:center;">
	<b>GRN SUMMARY</b>
	<h3>{$branch.report_prefix}{$grn.id|string_format:"%05d"}</h3>
</div>
<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
<div class=small style="padding-bottom:10px">{$branch.address|nl2br|default:"&nbsp;"}</div>
<table border=0 cellspacing=0 cellpadding=4 class="small tb" width=100%>
<tr>
	<td colspan=8 bgcolor=#eeeeee><h4>GRN Detail</h4></td>
</tr><tr>	
	<td><b>GRN No</b></td><td>{$branch.report_prefix}{$grn.id|string_format:"%05d"|default:"&nbsp;"}</td>
	<td><b>GRN Amount</b></td><td>{$grn.amount|number_format:2|default:"&nbsp;"}</td>
	<td><b>Adjustment</b></td><td>{$grn.buyer_adjustment+$grn.action_adjustment|number_format:2|default:"&nbsp;"}</td>
	<td><b>GRN Final Amt</b></td><td>{$grn.final_amount|number_format:2|default:"&nbsp;"}</td>
</tr><tr>
	<td><b>GRN Date</b></td><td>{$grn.added|date_format:$config.dat_format|default:"&nbsp;"}</td>
	<td><b>GRN By</b></td><td>{$grn.u|default:"&nbsp;"}</td>
	<td><b>GRN Qty</td></td><td {if !$grn.is_under_gst}colspan=3{/if}>{$grn_qty|default:"&nbsp;"}</td>
	{if $grn.is_under_gst}
		<td><b>GST Amount</td><td>{$grn.gst_amount|number_format:2}</td>
	{/if}
</tr><tr>
	<td><b>Invoice/DO No</b></td><td>{if !$config.use_grn_future || $grr.type eq 'PO'}{$grn.account_doc_no|default:"&nbsp;"}{else}{$grr.doc_no|default:"&nbsp;"}{/if}</td>
	<td><b>Invoice/DO Amount</b></td><td>{$grn.account_amount|number_format:2|default:"&nbsp;"}</td>
	{if $grn.is_under_gst}
		<td><b>D/N No.</b></td><td>{$grn.dn_number|default:"&nbsp;"}</td>
		<td><b>D/N Amount</b></td><td>{$grn.dn_amount|number_format:2|default:"&nbsp;"}</td>
	{else}
		<td colspan=4>&nbsp;</td>
	{/if}
</tr><tr>
	<td><b>Account Key In</b></td><td>{$grn.acc_u|default:"&nbsp;"}</td>
	<td><b>Account Date</b></td><td>{$grn.account_update|date_format:$config.dat_format|default:"&nbsp;"}</td>
	{if !$grn.is_under_gst}
		<td><b>Adjustment (CN/DN)</b></td><td>{$grn.buyer_adjustment|number_format:2|default:"&nbsp;"}</td>
	{/if}
	<td><b>Other Adjustment</b></td><td>{$grn.action_adjustment|number_format:2|default:"&nbsp;"}</td>
	{if $grn.is_under_gst}
		<td>&nbsp;</td><td>&nbsp;</td>
	{/if}
</tr><tr>
	<td><b>Action</b></td>
	<td colspan=7>{$grn.acc_action|nl2br|default:"&nbsp;"}</td>
</tr><tr>
	<td colspan=8 bgcolor=#eeeeee><h4>GRR Detail</h4></td>
</tr><tr>
	<td><b>GRR No</b></td><td>{$branch.report_prefix}{$grr.grr_id|string_format:"%05d"|default:"&nbsp;"}</td>
	<td><b>Total PO in GRR</b></td><td>{$total_po|default:"&nbsp;"}</td>
	<td><b>GRR Date</b></td><td>{$grr.added|date_format:$config.dat_format|default:"&nbsp;"}</td>
	<td><b>Key In By</b></td><td>{$grr.u|default:"&nbsp;"}</td>
</tr><tr>
	<td><b>GRR Amount</b></td><td>{$grr.grr_amount|number_format:2|default:"&nbsp;"}</td>
	<td><b>Received Qty</b></td><td>Ctn:{$grr.grr_ctn|number_format} / Pcs:{$grr.grr_pcs|number_format|default:"&nbsp;"}</td>
	<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format|default:"&nbsp;"}</td>
	<td><b>Received By</b></td><td>{$grr.rcv_u|default:"&nbsp;"}</td>
</tr><tr>
	<td><b>Vendor</b></td><td colspan=3>{$grr.vendor|default:"&nbsp;"}</td>
	<td><b>Department</b></td><td>{$grn.department|default:$grr.department}</td>
	<td><b>Lorry No</b></td><td>{$grr.transport|default:"&nbsp;"}</td>
</tr><tr>
	<td colspan=8 bgcolor=#eeeeee><h4>Document Detail</h4></td>
</tr><tr>
	<td><b>Document Type.</b></td><td>{$grr.type|default:"&nbsp;"}</td>
	<td><b>Document No.</b></td><td colspan=5>{$grr.doc_no|default:"&nbsp;"}</td>
{if $grr.type eq 'PO'}
</tr><tr>
	<td><b>PO Qty</b></td><td>{$po_qty|default:"&nbsp;"}</td>
	<td><b>Ordered By</b></td><td>{$grr.po_u|default:"&nbsp;"}</td>
	<td><b>PO Approvals</b></td><td colspan=3>{$grr.flow_approvals|get_user_list|default:"&nbsp;"}</td>
</tr><tr>
	<td><b>PO Amount</b></td><td>{$grr.po_amount|number_format:2|default:"&nbsp;"}</td>
	<td><b>Partial Delivery</b></td><td>{if $config.use_grn_future}{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}{else}{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}{/if}</td>
	<td><b>PO Date</b></td><td>{$grr.po_date|date_format:$config.dat_format|default:"&nbsp;"}</td>
	<td><b>Cancellation Date</b></td><td>{$grr.cancel_date|date_format:$config.dat_format|default:"&nbsp;"}</td>
{/if}
</tr>
</table>
<br>

<table width=100%>
<tr>
	<td width= "{if $grn.is_under_gst}35%{else}40%{/if}">
		<table width=100% style="border-collapse: collapse">
			<tr {if !$grn.is_under_gst}height=41{/if} align=center><th></th>
				<th>Rounding Error<br>Adjustment</th>
				<th>After Adjust</th></tr>
				{if $grn.is_under_gst}
					<tr align=center style="border: 1px solid black">
						<th>GST</th>
						<td>{$grn.rounding_gst_amt|number_format:2}</td>
						<td>{$grn.rounding_gst_amt+$grn.final_gst_amt|number_format:2}</td>
					</tr>
				{/if}
			<tr align=center style="border: 1px solid black">
				<th>GRN Amount{if $grn.is_under_gst}<br>incl GST{/if}</th>
				{assign var="final_adjustment_amount" value=$grn.account_amount}
				<td>{$grn.rounding_amt|number_format:2}</td>
				<td>{$grn.rounding_amt+$grn.final_amount|number_format:2}</td>
			</tr>
		</table>
	</td>
	<td width= "{if $grn.is_under_gst}65%{else}60%{/if}">
		<table width=100% style="border-spacing:8px; table-layout:fixed">
			<tr align=center>
				<th>INV/DO Amount</th>
				<th>(-) D/N<br>Amount</th>
				<th>(+/-) Adjustment<br>Amount</th>
				<th>Final Adjustment<br>Amount</th>
			</tr>
			<tr {if $grn.is_under_gst}height=29{/if} align=center>
				<td style="border: 1px solid black">{$grn.account_amount|number_format:2}</td>
				{assign var="final_adjustment_amount" value=$final_adjustment_amount-$grn.dn_amount}
				<td style="border: 1px solid black">{$grn.dn_amount|number_format:2}</td>
				{assign var="final_adjustment_amount" value=$final_adjustment_amount+$grn.buyer_adjustment+$grn.action_adjustment}
				<td style="border: 1px solid black">{$grn.buyer_adjustment+$grn.action_adjustment|number_format:2}</td>
				<td style="border: 1px solid black">{$final_adjustment_amount|number_format:2}</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<table width=100%>
<tr>
	<td align="center" width=10%><b>Action</b></td>
	<td width=90% colspan=8 style="border:1px solid #000">{$grn.acc_action|nl2br}</td>
</tr>
</table>

<br>
{if $grn.have_variance or $grn.account_amount != $grn.final_amount}
<table class="box small" width=100% cellpadding=2 cellspacing=0 border=0>
<tr class="topline botline" bgcolor=#cccccc>
	<th rowspan=2>&nbsp;</th>
	<th rowspan=2>ARMS Code</th>
	<th rowspan=2>Art/<br>MCode</th>
	<th rowspan=2>Description</th>
	<th rowspan=2>Selling Price</th>
	{if $grr.type eq 'PO'}
	<th rowspan=2>PO Price</th>
	<th rowspan=2>Nett Price</th>
	<th rowspan=2>PO Qty<br>(Pcs)</th>
	<th rowspan=2>Received<br>(Pcs)</th>
	<th rowspan=2>GRN/PO<br>Var</th>
	{/if}
	{if $grn.amount != $grn.account_amount}
		<th colspan=3>Invoiced</th>
	{/if}
	{if $grn.is_under_gst}
		<th rowspan=2>GST Code</th>
		<th rowspan=2>GST</th>
	{/if}
	{if $grn.amount != $grn.account_amount}
		<th rowspan=2>ACC/GRN Var<br>(Pcs)</th>
	{/if}
</tr>
<tr class="botline" bgcolor=#cccccc>
	{if $grn.amount != $grn.account_amount}
	<th>Price</th>
	<th>Ctn</th>
	<th>Pcs</th>
	{/if}
</tr>
<tbody id=tbditems>
{assign var=total_var1 value=0}
{assign var=total_var2 value=0}
{assign var=var1 value=0}
{assign var=var2 value=0}
{foreach name=i from=$grn_items item=item key=iid}

{assign var=po_qty value=`$item.po_ctn*$item.po_uomf+$item.po_pcs`}
{assign var=grn_qty value=$item.uom_fraction*$item.ctn+$item.pcs}
{assign var=var1 value=$grn_qty-$po_qty}
{assign var=var1 value=$var1|qty_nf}

{if $item.acc_ctn ne '' || $item.acc_pcs ne ''}
{assign var=var2 value=$item.uom_fraction*$item.acc_ctn+$item.acc_pcs-$grn_qty}
{else}
{assign var=var2 value=0}
{/if}
{assign var=var2 value=$var2|qty_nf}

{if $var1!=0 or $var2!=0}
<!-- {$n++} -->
<tr height=20 bgcolor="{cycle name=r1 values=",#eeeeee"}" {if $save_type && $item.rcc_status}style="text-decoration:line-through"{/if}>
	{if !$page_item_info.$iid.not_item}
		<td>{$item.item_no+1}.</td>{*<td>{$n}.</td>*}
		<td>{$item.sku_item_code}</td>
		<td>{$item.artno|default:"-"}/<br>{$item.mcode|default:"-"}</td>
	{else}
		<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	{/if}
	<td nowrap>{$item.description}</td>
	
	{if !$page_item_info.$iid.not_item}
		<td align=right>{$item.selling_price|number_format:2}</td>
		{if $grr.type eq 'PO'}
		<td align=right>{$item.po_cost|number_format:$config.global_cost_decimal_points}</td>
		<td align=right>{$item.cost|number_format:$config.global_cost_decimal_points}</td>
		<td align=center>{$item.po_qty|qty_nf|ifzero:"&nbsp;"}</td>
		<td align=center>{$grn_qty|qty_nf|ifzero:"&nbsp;"}</td>
		<td align=center>{if $var1>0}+{$var1|qty_nf}{else}{$var1|qty_nf|ifzero:"&nbsp;"}{/if}</td>
		{/if}
		{if $grn.amount != $grn.account_amount}
			{if $item.acc_ctn ne '' || $item.acc_pcs ne '' || $item.acc_cost ne ''}
			<td align=center>{$item.acc_cost|number_format:$config.global_cost_decimal_points|ifzero:"&nbsp;"}</td>
			<td align=center>{$item.acc_ctn|qty_nf|ifzero:"&nbsp;"}</td>
			<td align=center>{$item.acc_pcs|qty_nf|ifzero:"&nbsp;"}</td>
			{else}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			{/if}
		{/if}
		
		{if $grn.is_under_gst}
			{if $item.acc_gst_code}
				{assign var=acc_gst_code value=$item.acc_gst_code}
				{assign var=acc_gst_rate value=$item.acc_gst_rate}
			{else}
				{assign var=acc_gst_code value=$item.gst_code}
				{assign var=acc_gst_rate value=$item.gst_rate}
			{/if}
			<td align=center>{$acc_gst_code}</td>
			<td align=right>{$acc_gst_rate|default:'0'}%</td>
		{/if}
		
		{if $grn.amount != $grn.account_amount}
			{if $item.acc_ctn ne '' || $item.acc_pcs ne ''}
			<td align=center>{if $var2>0}+{$var2|qty_nf}{else}{$var2|qty_nf|ifzero:"&nbsp;"}{/if}</td>
			{assign var=total_pcs2 value=$total_pcs2+$item.acc_pcs|qty_nf}
			{assign var=total_ctn2 value=$total_ctn2+$item.acc_ctn|qty_nf}
			{else}
			<td>&nbsp;</td>
			{assign var=total_pcs2 value=$total_pcs2+$item.pcs|qty_nf}
			{assign var=total_ctn2 value=$total_ctn2+$item.ctn|qty_nf}
			{/if}
		{/if}
	{else}
		<td>&nbsp;</td>
		{if $grr.type eq 'PO'}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{/if}
		{if $grn.amount != $grn.account_amount}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{/if}
		{if $grn.is_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	{/if}
</tr>
{/if}
{assign var=total_pcs value=$total_pcs+$item.po_pcs|qty_nf}
{assign var=total_ctn value=$total_ctn+$item.po_ctn|qty_nf}
{assign var=total_pcs1 value=$total_pcs1+$item.pcs|qty_nf}
{assign var=total_ctn1 value=$total_ctn1+$item.ctn|qty_nf}
{math equation=y+abs(x) y=$total_var1 x=$var1 assign=total_var1}
{math equation=y+abs(x) y=$total_var2 x=$var2 assign=total_var2}
{/foreach}

{repeat s=$n+1 e=15}
<!-- filler -->
<tr height=20 bgcolor="{cycle name=r1 values=',#eeeeee'}">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $grr.type eq 'PO'}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{/if}
	{if $grn.amount != $grn.account_amount}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{/if}
	{if $grn.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/repeat}
</tbody>
<tr class="topline" height=20>
	<td {if $grr.type eq 'PO'}colspan=9{else}colspan=5{/if} align=right>Total&nbsp;</td>
	{if $grr.type eq 'PO'}
		<td align=center>{$total_var1|qty_nf}</td>
	{/if}
	{if $grn.amount != $grn.account_amount}
		<td colspan=3>&nbsp;</td>
	{/if}
	{if $grn.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	{if $grn.amount != $grn.account_amount}
		<td align=center>{$total_var2|qty_nf}</td>
	{/if}
</tr>
</table>

{/if}

{if $grr.sdiscount_amt || $grr.rdiscount_amt || $grr.po_remark1[0] || $grr.po_remark2[0]}
<!-- show D/N table if there is discount from remark in PO  -->
<br>
<table width=100% class="tb" cellpadding=4 cellspacing=0 border=0>
<tr height=50>
<td valign=top>
<b>PO Remark #1 (Discount Amt ({$grr.sdiscount}): {$grr.sdiscount_amt|number_format:2|ifzero:"-"})</b><br>
<span class=small>{$grr.po_remark1[0]|nl2br}</span>
</td>
<td valign=top>
<b>PO Remark #2 (Discount Amt ({$grr.rdiscount}): {$grr.rdiscount_amt|number_format:2|ifzero:"-"})</b><br>
<span class=small>{$grr.po_remark2[0]|nl2br}</span>
</td>
{if !$grn.is_under_gst}
	<td valign=top nowrap>
		{if $grn.dn_issued}
		<b>D/N Number:</b> {$grn.dn_number}<br>
		<b>D/N Amount:</b> {$config.arms_currency.symbol}{$grn.dn_amount|number_format:2}
		{else}
		{$grn.dn_reason|nl2br}
		{/if}
	</td>
{/if}
</tr>
</table>
<br>
{/if}

</div>

{if $grn.non_sku_items && $config.use_grn_future && $print_returned_items}
	<div class="printarea">
		<br><h2>Returned Item(s)</h2>
		<table class="box small" width="100%" class="tb" cellpadding=2 cellspacing=1 border=0>
			<tr  class="topline botline" bgcolor="#cccccc" height="20">
				<th>#</th>
				<th width="20%">Code</th>
				<th width="60%">Description</th>
				<th>Cost Price</th>
				<th>Rcv<br />Qty (Pcs)</th>
				<th>Amount</th>
			</tr>

			{foreach from=$grn.non_sku_items key=sku_code item=item name=fitem}
				{assign var=n value=$smarty.foreach.fitem.iteration-1}
				{if $grn.non_sku_items.code.$n}
					<!-- {$t++} -->
					{assign var=ttl_pcs value=$ttl_pcs+$grn.non_sku_items.qty.$n}
					{assign var=curr_amt value=$grn.non_sku_items.qty.$n*$grn.non_sku_items.cost.$n}
					{assign var=ttl_amt value=$ttl_amt+$curr_amt|round2}
					<tr height="20">
						<td nowrap width="2%" align="right">{$smarty.foreach.fitem.iteration}.</td>
						<td>{$grn.non_sku_items.code.$n}</td>
						<td>{$grn.non_sku_items.description.$n}</td>
						<td align="right">{$grn.non_sku_items.cost.$n|number_format:$config.global_cost_decimal_points:".":""}</td>
						<td align="right" width="5%">{$grn.non_sku_items.qty.$n|default:0}</td>
						<td align="right" width="5%">{$curr_amt|round2}</td>
					</tr>
				{/if}
			{/foreach}
		
			{repeat s=$t+1 e=20}
				<!-- filler -->
				<tr height="20">
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			{/repeat}

			<tr class="topline" height="20">
				<td colspan="4" align=right><b>Total</b></td>
				<td align="right">{$ttl_pcs|default:0}</td>
				<td align="right">{$ttl_amt|default:0}</td>
			</tr>
			
		</table>
	</div>
{/if}
