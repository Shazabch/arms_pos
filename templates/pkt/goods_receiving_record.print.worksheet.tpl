{*
4/5/2010 3:22:43 PM Andy
- Fix Foc ctn and pcs wrong column problem

7/15/2011 2:53:37 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs not to round up fixed by 2.

8/18/2011 12:12:32 PM Justin
- Fixed the empty row looping bugs that not working properly.

10/20/2011 10:45:43 AM Justin
- Added "#" indicator on PO Suggested Selling field if found different with current selling price.

4/3/2013 3:03 PM Justin
- Bug fixed on calculating cost and qty.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print
*}

{if !$skip_header}
{include file='header.print.tpl'}

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

<p>* PO contains FOC item will show out GRN Cost column.</p>

<table class="box small" cellpadding=1 cellspacing=0 border=1 width=100%>
<tr class="topline botline" height=24 bgcolor=#cccccc>
	<th>&nbsp;</th>
	<th>Art/MCode<br>ARMS Code
	{if $config.sku_application_require_multics}
	<br>{$config.link_code_name}
	{/if}
	</th>
	<th width=50%>Description</th>
	<th width=40>UOM</th>
	{if $po.is_foc}
	<th width=40>GRN Cost<br>(RM)</th>	
	{/if}
	{if $po.grn_po_qty}
	{*
	<th>Current<br>Selling</th>
	<th>PO<br>Selling</th>
	*}
	<th nowrap>PO<br>Suggested<br>Selling(RM)</th>
	<th width=40>Current<br>Selling<br>(RM)</th>
	<th width=40>PO Cost<br>(RM)</th>
	<th width=40>PO Qty<br>Pcs</th>
	{/if}
	{if $po.show_foc}
	    <th width=40>Foc Qty<br>(Ctn)</th>
		<th width=40>Foc Qty<br>(Pcs)</th>
	{/if}
	<th width=40>Rcv Qty<br>(Ctn)</th>
	<th width=40>Rcv Qty<br>(Pcs)</th>
</tr>

{section name=i loop=$po_items}
<tr height=24 bgcolor="{cycle name=r1 values=',#eeeeee'}">
	<td nowrap>{$start_counter+$smarty.section.i.iteration}. {if $po_items[i].is_foc_row}*{/if}</td>
	<td nowrap>{$po_items[i].artno_mcode|default:"&nbsp;"}<br>
	{$po_items[i].sku_item_code}
	{if $config.sku_application_require_multics}
	<br>{$po_items[i].link_code|default:"&nbsp;"}
	{/if}
	</td>
	<td>{$po_items[i].description|default:"&nbsp;"} 
	{if $po_items[i].remark}{$po_items[i].remark|escape}{/if}
	{if $po_items[i].remark2}{$po_items[i].remark2|escape}{/if}
	</td>
	<td align=center>{$po_items[i].order_uom}</td>
	
	{if $po.is_foc}
	<td align=right>
		{if $po_items[i].is_foc}
		{$po_items[i].grn_cost|default:$po_items[i].sku_cost|number_format:$config.global_cost_decimal_points}
		{else}
		 - 
		{/if}
	</td>
	{/if}
	
	{if $po.grn_po_qty}
	{*
	    {if $po_items[i].master_selling_price != $po_items[i].selling_price}
			<td align=right>{$po_items[i].master_selling_price|number_format:2}</td>
			<td align=right>{$po_items[i].selling_price|number_format:2}</td>
	    {else}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	*}
		{assign value=$po_items[i].ttl_qty var=qty}
		{assign value=$tqty+$qty var=tqty}
		{assign value=$po_items[i].ttl_cost/$po_items[i].ttl_qty var=cost}
		
		<td align=right nowrap>
		{$po_items[i].po_sell|number_format:2|ifzero:"&nbsp;"}
		{if $item.po_sell != $item.curr_sell}<sup style="font-size:7px;">#</sup>{/if}
		</td>		
		<td align=right>
		{$po_items[i].curr_sell|number_format:2}
		{if $cost>$po_items[i].curr_sell}*{/if}
		</td>		
		<td align=right>
		{$cost|default:$po_items[i].grn_cost|number_format:$config.global_cost_decimal_points|ifzero:"&nbsp;"}
		</td>		
		<td align=right>{$qty|qty_nf|ifzero:"&nbsp;"}</td>
	{/if}
	{if $po.show_foc}
	    {if $po_items[i].is_foc_row}
			<td align=right>{$po_items[i].foc|qty_nf|ifzero:'&nbsp;'}</td>
			<td align=right>{$po_items[i].foc_loose|qty_nf|ifzero:'&nbsp;'}</td>
			
			{assign var=total_foc_loose value=$total_foc_loose+$po_items[i].foc_loose}
			{assign var=total_foc value=$total_foc+$po_items[i].foc}
	    {else}
	        <td>&nbsp;</td>
			<td>&nbsp;</td>
	    {/if}
	{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/section}

{assign var=s2 value=$n}
{section name=s start=$n loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<!-- filler -->
<tr height=24 bgcolor="{cycle name=r1 values=',#eeeeee'}">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
{*	<td>&nbsp;</td> *}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $po.is_foc}
	<td>&nbsp;</td>	
	{/if}
	{if $po.grn_po_qty}
	{*
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	*}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{/if}
	{if $po.show_foc}
	    <td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/section}

<tr class="topline" height=24>
{assign value=4 var=colspan}

{if $po.is_foc}{assign value=$colspan+1 var=colspan}{/if}
{if $po.grn_po_qty}
	{assign value=$colspan+3 var=colspan}
{/if}


	<td colspan="{$colspan}" align=right>Total&nbsp;</td>
	{if $po.grn_po_qty}
		<td align=right>{$tqty|qty_nf}</td>
	{/if}
	{if $po.show_foc}
	    <td align=right>{$total_foc|qty_nf|default:0}</td>
        <td align=right>{$total_foc_loose|qty_nf|default:0}</td>
	{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
</table>
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
</div>
