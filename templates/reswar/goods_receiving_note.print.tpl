{*
7/12/2011 1:11:40 PM Justin
- Fixed the rounding error for total amount.

7/15/2011 3:29:25 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

7/5/2012 4:33:34 PM Justin
- Enhanced to show between PO and DO at the header.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

11/18/2014 5:26 PM Justin
- Enhanced to show GST column and calculation.
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
<table class=small align=right cellpadding=4 cellspacing=0 border=0>
<tr bgcolor=#cccccc>
	<td align=center><b>GOODS RECEIVING NOTE<br>VARIANCE REPORT<br>{if $grn.status eq 0}(Not Verified){/if}</b></td>
</tr>
<tr bgcolor=#cccccc>
	<td align=center><b>{$page}</b></td>
</tr>
</table>
<h2>{$branch.description}</h2>
<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>

<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
<tr>
<td><b>GRN No</b></td><td>GRN{$grn.id|string_format:"%05d"}</td>
<td><b>GRN Date</b></td><td>{$grn.added|date_format:$config.dat_format}</td>
<td><b>GRN By</b></td><td>{$grn.u}</td>
<td><b>Printed By</b></td><td>{$sessioninfo.u}</td>
</tr><tr>
<td><b>GRR No</b></td><td>GRR{$grn.grr_id|string_format:"%05d"}/{$grn.grr_item_id}</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:$config.dat_format}</td>

<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
<td><b>Received By</b></td><td>{$grr.rcv_u}</td>
</tr><tr>
<td><b>Vendor</b></td><td colspan=3>{$grr.vendor}</td>
<td><b>Department</b></td><td colspan=3>{$grn.department|default:$grr.department}</td>
</tr><tr>
<td><b>Lorry No</b></td><td>{$grr.transport}</td>
<td><b>Document Type.</b></td><td><font color=blue>{$grr.type}</font></td>
<td><b>Document No.</b></td><td><font color=blue>{$grr.doc_no}</font></td>
{if $grr.type eq 'PO'}
<td><b>Partial Delivery</b></td><td>{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}</td>
{else}
<td colspan=2>&nbsp;</td>
{/if}
</tr>

</table>

<br>
<table class="box small tb" width=100% cellpadding=2 cellspacing=0 border=0>
<tr class="topline botline" bgcolor=#cccccc>
	<th rowspan=2>&nbsp;</th>
	<th rowspan=2>ARMS/<br>Mcode</th>
	<th rowspan=2>Artno</th>
	<!--<th rowspan=2>Mcode</th>-->
	<th rowspan=2>Description</th>
	<th rowspan=2 width=45>Order<br>Price</th>
	{if $grr.type eq 'PO' || $grr.is_ibt_do}
		<th colspan=3>
			{if $grr.type eq 'PO' || !$grr.is_ibt_do}
				Purchased
			{else}
				Delivered
			{/if}
		</th>
		<th colspan=2>FOC</th>
		<th rowspan=2 width=45>Amount</th>
		{if $po_under_gst}
			<th rowspan=2>GST Code</th>
			<th rowspan=2>GST</th>
			<th rowspan=2>Amount<br />Incl. GST</th>
		{/if}
	{/if}
	<th colspan=3>Received</th>
	<th rowspan=2 width=45>Amount</th>
	{if $grn.is_under_gst}
		<th rowspan=2>GST Code</th>
		<th rowspan=2>GST</th>
		<th rowspan=2>Amount<br />Incl. GST</th>
	{/if}
	<th rowspan=2 width=45>Variance<br>(Pcs)</th>
</tr>
<tr class="botline" bgcolor=#cccccc>
	{if $grr.type eq 'PO' || $grr.is_ibt_do}
	<th width=45>UOM</th>
	<th width=45>Ctn</th>
	<th width=45>Pcs</th>
	<th width=45>Ctn</th>
	<th width=45>Pcs</th>
	{/if}
	<th width=45>UOM</th>
	<th width=45>Ctn</th>
	<th width=45>Pcs</th>
</tr>
<tbody id=tbditems>
{assign var=total value=0}
{assign var=tctn value=0}
{assign var=tpcs value=0}
{assign var=tpctn value=0}
{assign var=tppcs value=0}
{assign var=n value=0}

{foreach name=i from=$grn_items item=item key=iid}
{assign var=qty value=`$item.ctn*$item.uom_fraction+$item.pcs`}
{assign var=row_total value=`$item.cost*$qty/$item.uom_fraction`}
{assign var=total value=$total+$row_total|round:2}
{assign var=tctn value=`$tctn+$item.ctn`}
{assign var=tpcs value=`$tpcs+$item.pcs`}
{if $grr.type eq 'PO' || $grr.is_ibt_do}
{assign var=qty2 value=`$item.po_order_ctn*$item.po_uomf+$item.po_order_pcs`}
{assign var=row_total2 value=`$item.cost*$qty2/$item.po_uomf`}
{assign var=total2 value=$total2+$row_total2|round:2}
{assign var=tpctn value=`$tpctn+$item.po_ctn`}
{assign var=tppcs value=`$tppcs+$item.po_pcs`}
{/if}
<!-- {$n++} -->
<tr height=30 bgcolor="{cycle name=r1 values=",#eeeeee"}" class="no_border_bottom">
	<td>{$start_counter+$n}.</td>
	<td>{$item.sku_item_code}{if $item.mcode<>''}<br>{$item.mcode|default:"-"}{/if}</td>
	<td align=center>{$item.artno|default:"-"}</td>
	<!--<td align=center>{$item.mcode|default:"-"}</td>-->
	<td><div class="crop" style="height:2em">{$item.description}</div></td>
	<td align=right>{$item.cost|number_format:$config.global_cost_decimal_points}</td>
	{if $grr.type eq 'PO' || $grr.is_ibt_do}
		<td>{$item.po_uom|default:"&nbsp;"}</td>
		<td align=right>{$item.po_order_ctn|qty_nf}</td>
		<td align=right>{$item.po_order_pcs|qty_nf}</td>
		<td align=right>{$item.po_foc_ctn|qty_nf}</td>
		<td align=right>{$item.po_foc_pcs|qty_nf}</td>
		<td align=right>{$row_total2|number_format:2}</td>
		{if $po_under_gst}
			{if $item.po_gst_id}
				{assign var=po_gst_amt value=$item.cost*$item.po_gst_rate/100}
				{assign var=po_gst_amt value=$po_gst_amt|round:$config.global_cost_decimal_points}
				{assign var=po_row_gst value=$po_gst_amt*$qty2}
				{assign var=po_row_gst value=$po_row_gst|round2}
				<td align=right nowrap>
					{$item.po_gst_code} ({$item.po_gst_rate|default:'0'}%)<br />
					{$po_gst_amt|number_format:$config.global_cost_decimal_points}
				</td>
				{assign var=po_row_gst_cost value=$row_total2+$po_row_gst}
				{assign var=po_total_gst value=$po_total_gst+$po_row_gst}
				{assign var=po_total_gst_cost value=$po_total_gst_cost+$po_row_gst_cost}
				<td align=right>{$po_row_gst|number_format:2}</td>
				<td align=right>{$po_row_gst_cost|number_format:2}</td>
			{else}
				<td align="center">-</td>
				<td align="center">-</td>
				<td align="center">-</td>
			{/if}
		{/if}
	{/if}
	<td>{$item.order_uom}</td>
	<td align=right>{$item.ctn|qty_nf}</td>
	<td align=right>{$item.pcs|qty_nf}</td>
	<td align=right>{$row_total|number_format:2}</td>
	{if $grn.is_under_gst}
		{assign var=gst_amt value=$item.cost*$item.gst_rate/100}
		{assign var=gst_amt value=$gst_amt|round:$config.global_cost_decimal_points}
		{assign var=row_gst value=$gst_amt*$qty}
		{assign var=row_gst value=$row_gst|round2}
		<td align=right nowrap>
			{$item.gst_code} ({$item.gst_rate|default:'0'}%)<br />
			{$gst_amt|number_format:$config.global_cost_decimal_points}
		</td>
		{assign var=row_gst_cost value=$row_total+$row_gst}
		{assign var=total_gst value=$total_gst+$row_gst}
		{assign var=total_gst_cost value=$total_gst_cost+$row_gst_cost}
		<td align=right>{$row_gst|number_format:2}</td>
		<td align=right>{$row_gst_cost|number_format:2}</td>
	{/if}
	<td align=right bgcolor="{cycle name=r2 values='#eeeeee,#dddddd'}">
	{if $qty>$item.po_qty}
	{assign var=tvar value=$tvar+$qty-$item.po_qty|round:$config.global_qty_decimal_points}
	+{$qty-$item.po_qty|qty_nf}
	{else}
	{assign var=tvar value=$tvar+$item.po_qty-$qty|round:$config.global_qty_decimal_points}
	{$qty-$item.po_qty|qty_nf}
	{/if}
	</td>
</tr>
{/foreach}

{assign var=s2 value=$n}
{section name=s start=$n loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height=30 bgcolor="{cycle name=r1 values=',#eeeeee'}" class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
  	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $grr.type eq 'PO' || $grr.is_ibt_do}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if $po_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	{/if}
	{if $grn.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	<td bgcolor="{cycle name=r2 values="#eeeeee,#dddddd"}">&nbsp;</td>
</tr>
{/section}

{*{repeat s=$n+1 e=$PAGE_SIZE}
<!-- filler -->
<tr height=30 bgcolor="{cycle name=r1 values=",#eeeeee"}" class="no_border_bottom">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<!--<td>&nbsp;</td>-->
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $grr.type eq 'PO' || $grr.is_ibt_do}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{/if}
	<td bgcolor="{cycle name=r2 values="#eeeeee,#dddddd"}">&nbsp;</td>
</tr>
{/repeat}*}


</tbody>
<tr class="topline" height=24 bgcolor=#cccccc>
<td colspan=5 align=right><b>Total</b></td>
{if $grr.type eq 'PO' || $grr.is_ibt_do}
	<td colspan=5 align=right>Ctn:{$tpctn|qty_nf} Pcs:{$tppcs|qty_nf}</td>
	<td align=right>{$total2|number_format:2}</td>
	{if $po_under_gst}
		<td>&nbsp;</td>
		<td align=right>{$po_total_gst|number_format:2}</td>
		<td align=right>{$po_total_gst_cost|number_format:2}</td>
	{/if}
{/if}
<td colspan=3 align=right>Ctn:{$tctn|qty_nf} Pcs:{$tpcs|qty_nf}</td>
<td align=right>{$total|number_format:2}</td>
{if $grn.is_under_gst}
	<td>&nbsp;</td>
	<td align=right>{$total_gst|number_format:2}</td>
	<td align=right>{$total_gst_cost|number_format:2}</td>
{/if}
<td bgcolor=#bbbbbb align=right>{$tvar|qty_nf}</td>
</tr>
</table>

</div>

