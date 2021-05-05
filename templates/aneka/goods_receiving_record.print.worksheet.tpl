{*
8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

9/12/2011 10:30:56 AM Andy
- Create a new GRR worksheet printing template for Aneka.
- Move "PO Suggested Selling" & "Current Selling" to show all the time.
- Remove "GRN Cost" column.

9/21/2011 4:32:32 PM Justin
- Fixed the cost comparison to assign "*" bugs.

9/30/2011 6:43:43 PM Justin
- Modified the PO Cost to include FOC qty as well.

10/20/2011 10:45:43 AM Justin
- Added "#" indicator on PO Suggested Selling field if found different with current selling price.

5/10/2013 4:35 PM Andy
- Fix po qty and cost.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print
*}

{if !$skip_header}
{include file='header.print.tpl'}

<style>
{if $config.grr_worksheet_printing_no_item_line}
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
var doc_no = 'GRR{$grr.grr_id|string_format:"%05d"}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}

<!-- print work sheet -->
<div class=printarea>

<h2>{$branch.description}</h2>
<table class=small align=right cellpadding=4 cellspacing=0 border=0>
<tr bgcolor=#cccccc>
	<td align=center nowrap><b>GOODS RECEIVING NOTE<br>WORKSHEET</b><br>{$page}</td>
</tr>
</table>
<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>
<br>
<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
<tr>
	<td nowrap><b>Supplier</b></td>
	<td colspan=3>{$grr.vendor}</td>
	<td nowrap><b>GRR No</b></td>
	<td nowrap>GRR{$grr.grr_id|string_format:"%05d"}</td>
</tr>
<tr>
	<td nowrap><b>Department</b></td>
	<td colspan=3 nowrap>{$po.dept|default:"-"}</td>
	<td nowrap><b>Print By</b></td>
	<td nowrap>{$sessioninfo.fullname}</td>
</tr>
<tr>
	<td nowrap><b>Lorry No</b></td>
	<td colspan=3 nowrap>{$grr.transport}</td>
	<td nowrap><b>Print Date</b></td>
	<td nowrap>{$smarty.now|date_format:$config.dat_format}</td>
</tr>
<tr>
	<td nowrap><b>P/O No</b></td>
	<td nowrap>{$po.po_no}</td>
	<td nowrap><b>Delivery Date</b></td>
	<td nowrap>{$po.delivery_date}</td>
	<td nowrap><b>Cancellation Date</b></td>
	<td nowrap>{$po.cancel_date}</td>
</tr>
</table>
<br>

<table class="box small tb" cellpadding=1 cellspacing=0 border=0 width=100%>
<tr class="" height=24 bgcolor=#cccccc>
	<th>&nbsp;</th>
	<th>Art/MCode<br>ARMS Code
	{if $config.sku_application_require_multics}
	<br>{$config.link_code_name}
	{/if}
	</th>
	<th width=50%>Description</th>
	<th width=40>UOM</th>
	{if $po.is_foc}
		{*<th width=40>GRN Cost<br>(RM)</th>*}	
	{/if}
	
	<th nowrap>PO<br>Suggested<br>Selling(RM)</th>
	<th width=40>Current<br>Selling<br>(RM)</th>
	
	{if $po.grn_po_qty}		
		<th width=40>PO Cost<br>(RM)</th>
		<th width=40>PO Qty<br>Pcs</th>
	{/if}
	<th width=40>Rcv Qty<br>(Ctn)</th>
	<th width=40>Rcv Qty<br>(Pcs)</th>
</tr>

{assign var=n value=0}
{foreach name=i from=$po_items item=item key=iid}
	<!-- {$n++} -->
	<tr height=24 bgcolor="{cycle name=r1 values=",#eeeeee"}" class="no_border_bottom">
		<td nowrap>{$start_counter+$smarty.foreach.i.iteration}.</td>
		<td nowrap>{$item.artno_mcode|default:"&nbsp;"}<br>
		{$item.sku_item_code}
		{if $config.sku_application_require_multics}
		<br>{$item.link_code|default:"&nbsp;"}
		{/if}
		</td>
		<td>{$item.description|default:"&nbsp;"} 
		{if $item.remark}{$item.remark|escape}{/if}
		{if $item.remark2}{$item.remark2|escape}{/if}
		</td>
		<td align=center>{$item.order_uom}</td>
		
		{if $po.is_foc}
		{*<td align=right>
			{if $item.is_foc}
			{$item.grn_cost|default:$item.sku_cost|number_format:$config.global_cost_decimal_points}
			{else}
			 - 
			{/if}
		</td>*}
		{/if}
		
		<td align=right nowrap>
			{$item.po_sell|number_format:2|ifzero:"&nbsp;"}
			{if $item.po_sell != $item.curr_sell}<sup style="font-size:7px;">#</sup>{/if}
		</td>		
		<td align=right>
			{* assign value=$item.order_price/$item.order_uom_fraction var=cost}
			{*
			{assign value=$item.qty+$item.foc var=ctn}
			{assign value=$ctn*$item.order_uom_fraction+$item.qty_loose+$item.foc_loose var=qty}
			*}
			{assign value=$item.ttl_qty var=qty}
			{assign value=$item.ttl_cost/$item.ttl_qty var=cost}
			
			{* assign value=$item.qty*$item.order_uom_fraction+$item.qty_loose var=po_wo_foc_qty}
			{assign value=$po_wo_foc_qty*$cost var=amount}
			{assign value=$amount/$qty var=cost *}
			{assign value=$tqty+$qty var=tqty}
			{$item.curr_sell|number_format:2}
			{if $cost>$item.curr_sell}*{/if}
		</td>
			
		{if $po.grn_po_qty}
				
			<td align=right>
				{$cost|default:$item.grn_cost|number_format:$config.global_cost_decimal_points|ifzero:"&nbsp;"}
			</td>		
			<td align=right>{$qty|qty_nf|ifzero:"&nbsp;"}</td>
		{/if}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
{/foreach}

{assign var=s2 value=$n}
{section name=s start=$n loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<!-- filler -->
<tr height=24 bgcolor="{cycle name=r1 values=',#eeeeee'}" class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td>&nbsp;</td>
	<td>&nbsp;</td>

	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>

	{if $po.is_foc}
		{*<td>&nbsp;</td>*}
	{/if}
	
	<td>&nbsp;</td>
	<td>&nbsp;</td>
		
	{if $po.grn_po_qty}	
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/section}

<tr class="topline" height="24">
{assign value=4 var=colspan}

{if $po.is_foc}
	{* assign value=$colspan+1 var=colspan *}
{/if}
{if $po.grn_po_qty}
	{* assign value=$colspan+1 var=colspan *}
{/if}
	<td colspan="{$colspan}" align="left">{* PO contains FOC item will show out GRN Cost column.*}&nbsp;</td>
	<td align="right">Total&nbsp;</td>

	<td>&nbsp;</td>
	
	{if $po.grn_po_qty}
		<td>&nbsp;</td>	
		<td align=right>{$tqty|qty_nf}</td>
	{/if}
	
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
</table>

{if $is_lastpage}
	<br>
	<table class="tbd xsmall" cellpadding=4 cellspacing=0 border=0 width=100%>
	<tr bgcolor=#cccccc>
		<th>Received By</th>
		<th>Name</th>
		<th>Signature</th>
		<th>Date</th>
		<th>Time</th>
	</tr>
	
	<tr height=35>
		<td><b>At Department</b></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	
	<tr height=35>
		<td><b>Verify By</b></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</table>
{/if}
</div>
