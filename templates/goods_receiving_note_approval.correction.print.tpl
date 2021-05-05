{*
REVISION HISTORY
++++++++++++++++++
9/20/2007 4:53:13 PM gary
- alter ARMS code and artno/mcode position.

10/18/2007 4:33:13 AM gary
- grn correction sheet add 1 columm for po unit cost * grn receiving qty (purpose for officer easy reference and checking compare ).
-Every grouping foc (buy a b c foc d) show out qty in grn worksheet
-correction sheet item qty variance without cost (foc item)should print out and keet track for further reference

10/24/2007 5:40:16 PM gary
- correction for grn cost.

10/21/2010 12:42:37 PM AM Justin
- Changed the font to smaller for header and footer.
- Modified the footer contents only print out on last page.
- Added page navigation.
- Modified the looping to print empty row based on the pages.
- Fixed the row no count follow by item instead of restart counting on every page.

11/10/2010 11:56:37 AM Justin
- Added the strike on row when having reconcile and user tick the include of reconcile status.

7/15/2011 1:43:32 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

2/22/2017 4:40 PM Justin
- Enhanced to show "Excluded GST" message for PO amount while it is under GST.

4/25/2017 2:54 PM Khausalya
- Enhanced changes from RM to use config setting. 

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

11/22/2018 10:26 AM Justin
- Enhanced to show Old Code instead of ARMS Code when config is turned on.
*}
{if !$skip_header}
{include file='header.print.tpl'}
<style>
{literal}
.no_border_bottom td{
	border-bottom:none !important;
}
{/literal}

</style>
<script type="text/javascript">
var doc_no = 'GRN{$grn.id|string_format:"%05d"}';
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
<table width=100% cellpadding=2 cellspacing=0 border=0>
<tr>
<td>
	<div class=small style="padding-bottom:2px">{$branch.address|nl2br}</div>
</td>
<td align=center>
	<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
</td>
<td>
	<div style="float:right;text-align:center;background:#ccc;">GRN CORRECTION SHEET <br />{$page}</div>
</td>
</tr>
</table>

<table class="tbd xsmall" cellpadding=4 cellspacing=0 border=0 width=100%>
<tr>
<td><b>GRN No</b></td><td>GRN{$grn.id|string_format:"%05d"}</td>
<td><b>GRN Amount</b></td><td><font class="hilite" color=red>{$grn.amount|number_format:2}</font></td>
<td><b>Account Amount</b></td><td><font class="hilite" color=red>{$grn.account_amount|number_format:2}</font></td>
<td><b>Lorry No</b></td><td>{$grr.transport}</td>
<tr>
<td><b>GRR No</b></td><td>GRR{$grr.grr_id|string_format:"%05d"}</td>
<td><b>GRR ID</b></td><td>#{$grr.grr_item_id}</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.u}</td>
</tr><tr>
<td><b>GRR Amount</b></td><td>{$grr.amount|number_format:2}</td>
<td><b>Received Qty</b></td><td>Ctn:{$grr.grr_ctn|number_format} / Pcs:{$grr.grr_pcs|number_format}</td>
<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.rcv_u}</td>
</tr><tr>
<td><b>Department</b></td><td colspan=3>{$grn.department|default:$grr.department}</td>
<td><b>Vendor</b></td><td colspan=3>{$grr.vendor}</td>
</tr><tr>
<td><b>Document Type.</b></td><td><font color=blue>{$grr.type}</font></td>
<td><b>Document No.</b></td><td><font color=blue>{$grr.doc_no}</font></td>
{if $grr.type eq 'PO'}
<td><b>PO Amount{if $grr.po_is_under_gst}<br />(Excluded GST){/if}</b></td><td><font color=blue>{$grr.po_amount|number_format:2}</font></td>
<td><b>Partial Delivery</b></td><td><font color=blue>{if $config.use_grn_future}{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}{else}{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}{/if}</font></td>
{/if}
</tr>
</table>

<div style="padding:5px 0">
<table class="box small" width=100% cellpadding=2 cellspacing=0 border=0>
<tr class="topline botline" bgcolor=#cccccc>
	<th rowspan=2>&nbsp;</th>
	<th rowspan=2>
		{if $config.replace_docs_arms_code_with_link_code}
			{$config.link_code_name|default:'Old Code'}
		{else}
			ARMS Code
		{/if}
	</th>
	<th rowspan=2>Art/MCode</th>
	<th rowspan=2 width="60%">Description</th>
	<th colspan=3>Purchased</th>
	<th>FOC</th>
	<th colspan=2>Received</th>
	<th rowspan=2>GRN Cost<br>({$config.arms_currency.symbol})</th>
	<th rowspan=2>GRN/PO<br>Var</th>
		
	{*if $form.amount != $form.account_amount}
	<th colspan=3>Invoiced</th>
	{/if*}
	<th rowspan=2>Action</th>
</tr>
<tr class="botline" bgcolor=#cccccc>
    <th>UOM</th>
    <th>Price<br>Nett</th>
	<th>Ctn<br>Pcs</th>
	
	<th>Ctn<br>Pcs</th>
	
    <th>UOM</th>
	<th>Ctn<br>Pcs</th>
	
	{*if $form.amount != $form.account_amount}
	<th>Price</th>
	<th>Ctn</th>
	<th>Pcs</th>
	{/if*}
</tr>
<tbody id=tbditems>
{assign var=total_var1 value=0}
{assign var=total_var2 value=0}
{assign var=var1 value=0}
{assign var=var2 value=0}
{assign var=counter value=0}

{foreach name=i from=$grn_items item=item key=iid}
<!-- {$counter++} -->
<tr height=16 bgcolor="{cycle name=r1 values=",#eeeeee"}" {if $smarty.request.reconcile && $item.rcc_status}style="text-decoration: line-through;"{/if}>
	<td>{$start_counter+$counter}.</td>
	<td>
		{if $config.replace_docs_arms_code_with_link_code}
			{$item.link_code|default:'&nbsp;'}
		{else}
			{$item.sku_item_code}
		{/if}
	</td>
	<td>{$item.artno|default:"-"}/<br>{$item.mcode|default:"-"}</td>
	<td>
	{if $item.is_foc}
	<sup  style="color:#f00">{$item.foc_id}</sup>
	{/if}
	{$item.description}
	{assign var=item_po_id value=$item.po_item_id}
	<sup style="color:#f00">{$foc_annotations.$item_po_id}</sup>
	</td>
	<td align=center>{$item.po_uom|default:$item.order_uom}</td>
	<td align=right>{$item.po_cost|number_format:2}<br>{$item.cost|number_format:2}</td>
	<td align=center>{$item.po_order_ctn|ifzero:"&nbsp;"}<br>{$item.po_order_pcs|ifzero:"&nbsp;"}</td>
	
	<td align=center>{$item.po_foc_ctn|ifzero:"&nbsp;"}<br>{$item.po_foc_pcs|ifzero:"&nbsp;"}</td>
	
	<td align=center>{$item.order_uom}</td>
	<td align=center>{$item.ctn|ifzero:"&nbsp;"}<br>{$item.pcs|ifzero:"&nbsp;"}</td>
	
	{assign var=po_qty value=`$item.po_ctn*$item.po_uomf+$item.po_pcs`}

	{assign var=grn_qty value=$item.uom_fraction*$item.ctn+$item.pcs}
	
	{assign var=grn_amt value=$item.cost*$grn_qty/$item.uom_fraction}
	<td align=right>{$grn_amt|number_format:2}</td>	
	
	{assign var=var1 value=$grn_qty-$po_qty}
	<td align=center>{if $var1>0}+{$var1}{else}{$var1|ifzero:"&nbsp;"}{/if}</td>

	
	{*if $form.amount != $form.account_amount}
		<!--
		{if $item.acc_ctn ne '' || $item.acc_pcs ne '' || $item.acc_cost ne ''}
		<td align=center>{$item.acc_cost|number_format:2|ifzero:"&nbsp;"}</td>
		<td align=center>{$item.acc_ctn|ifzero:"&nbsp;"}</td>
		<td align=center>{$item.acc_pcs|ifzero:"&nbsp;"}</td>
		{else}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{/if}
		{if $item.acc_ctn ne '' || $item.acc_pcs ne ''}
		{assign var=var2 value=$item.uom_fraction*$item.acc_ctn+$item.acc_pcs-$grn_qty}
		<td align=center>{if $var2>0}+{$var2}{else}{$var2|ifzero:"&nbsp;"}{/if}</td>
		{assign var=total_pcs2 value=$total_pcs2+$item.acc_pcs}
		{assign var=total_ctn2 value=$total_ctn2+$item.acc_ctn}
		{else}
		<td>&nbsp;</td>
		{assign var=total_pcs2 value=$total_pcs2+$item.pcs}
		{assign var=total_ctn2 value=$total_ctn2+$item.ctn}
		{/if}
		{if $item.acc_cost ne ''}
		{assign var=var3 value=$item.acc_cost-$item.po_cost}
		<td align=center>{if $var3>0}+{$var3|number_format:2}{else}{$var3|number_format:2|ifzero:"&nbsp;"}{/if}</td>
		{else}
		<td>&nbsp;</td>
		{/if}
		-->
	{/if*}
	
    <td>&nbsp;</td>
</tr>
{assign var=total_pcs value=$total_pcs+$item.po_pcs}
{assign var=total_ctn value=$total_ctn+$item.po_ctn}
{assign var=total_pcs1 value=$total_pcs1+$item.pcs}
{assign var=total_ctn1 value=$total_ctn1+$item.ctn}
{math equation=y+abs(x) y=$total_var1 x=$var1 assign=total_var1}
{math equation=y+abs(x) y=$total_var2 x=$var2 assign=total_var2}
{/foreach}
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
	<td>&nbsp;</td>
	{*if $form.amount != $form.account_amount}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{/if*}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/section}
</tbody>
{if $is_last_page}
	<tr class="topline" height=20>
		<td colspan=4 align=right>Total&nbsp;</td>
		<td colspan=4 align=center>Ctn: {$total_ctn} / Pcs: {$total_pcs}</td>
		<td colspan=2 align=center>Ctn: {$total_ctn1} / Pcs: {$total_pcs1}</td>
		<td>&nbsp;</td>
		<td align=center>{$total_var1}</td>
		{*if $form.amount != $form.account_amount}
		<td colspan=2 align=center>Ctn: {$total_ctn2} / Pcs: {$total_pcs2}</td>
		{/if*}
		<td><img src="/ui/pixel.gif" width=80 height=1></td>
	</tr>
	</table>
	</div>

	{if $grr.sdiscount[0] || $grr.rdiscount[0] || $grr.po_remark1[0] || $grr.po_remark2[0]}
		<!-- show D/N table if there is discount from remark in PO  -->
		<table width=100% class="tb xsmall" cellpadding=4 cellspacing=0 border=0>
		<tr height=50>
		<td valign=top>
		<b>PO Remark #1 (Discount Amt: {$grr.sdiscount[0]|default:"-"})</b><br>
		<span class=small>{$grr.po_remark1[0]|nl2br}</span>
		</td>
		<td valign=top>
		<b>PO Remark #2 (Discount Amt: {$grr.rdiscount[0]|default:"-"})</b><br>
		<span class=small>{$grr.po_remark2[0]|nl2br}</span>
		</td>
		<td valign=top nowrap>
		<img src="/ui/pixel.gif" width=20 align=absmiddle style="border:1px solid #000"> <b>D/N Issued</b> (Please tick)<br>
		<b>D/N Number:</b>____________<br>
		<b>D/N Amount:</b>{$config.arms_currency.symbol}__________<br>
		If not issued, write the reason:<br>
		<br>
		<br>
		</td>
		</tr>
		</table>
	{/if}
	<div style="padding:5px 0">
	<table cellpadding=4 cellspacing=0 border=0 width=100% class="tb xsmall">
	<tr>
		<td width=25%>
		<b>Account Department</b><br>
		Action:<br><br>
		Name/Signature:<br>
		Date:<br>
		</td>
		<td width=50%>
		<b>Sales Department</b><br>
		Action:<br><br>
		Name/Signature:<br>
		Date:<br>
		</td>
		<td width=25%>
		<b>Manager</b><br>
		Action:<br><br>
		Name/Signature:<br>
		Date:<br>
		</td>
	</tr>
	</table>
	</div>
{else}
	</table>
	</div>
{/if}

</div>
