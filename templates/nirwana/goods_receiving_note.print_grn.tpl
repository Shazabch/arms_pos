{*
7/19/2011 3:04:20 PM Justin
- Fixed the Received items that do not show all received qty due to the Ctn did not sum up with pcs.
- Added Total Cost for Received Item and SKU Not In ARMS tables.

7/26/2011 6:18:12 PM Justin
- Enhanced the report to have ctn and return ctn fields.
- Removed all the "Qty (Pcs)" which it is being replaced by Pcs and Ctn columns.

7/27/2011 11:54:42 AM Justin
- Fixed the PO Variance does not calculate properly.

7/28/2011 10:52:21 AM Justin
- Added back the missing Total Cost for Received Item after the previous updates.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

8/10/2011 2:24:21 PM Justin
- Added Cost for Item in PO devision.

2/28/2012 11:31:43 AM Justin
- Added new ability to show DO as PO format when it is IBT DO.

7/5/2012 4:33:34 PM Justin
- Enhanced to show between PO and DO at the header.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

11/18/2014 5:26 PM Justin
- Enhanced to show GST column and calculation.

3/25/2015 2:33 PM Justin
- Enhanced to have between nett selling price or GST selling price while doing current vs suggested selling price.
*}

{if !$skip_header}
{include file='header.print.tpl'}

<style>
{if $config.grn_printing_no_item_line}
{literal}
.topline {
	background-color:#ddd;
}

td div.crop{
  height:auto;
  max-height:2em;
  overflow:hidden;
}
.no_border_bottom td{
	border-bottom:none !important;
}
.total_row td, .total_row th{
    border-top: 1px solid #000;
}
.td_btm_got_line td,.td_btm_got_line th{
    border-bottom:1px solid black !important;
}

.line_strike{
	text-decoration: line-through;
}


{/literal}
{/if}
</style>
<script type="text/javascript">
var doc_no = 'GRN{$smarty.request.id|string_format:"%05d"}';
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
{* set tables that to be print on the list *}

{if ($grr.type eq 'PO' || $grr.is_ibt_do) && (!$div || $div eq '1')}
	{assign var=row_counter value=0}
	{assign var=tpctn value=0}
	{assign var=tppcs value=0}
	{assign var=tpcs value=0}
	{assign var=trctn value=0}
	{assign var=trpcs value=0}
	{assign var=var value=0}
	{assign var=tvar value=0}
	{assign var=n value=0}
	{assign var=header value=0}
	{assign var=stpctn value=0}
	{assign var=stppcs value=0}
	{assign var=strctn value=0}
	{assign var=strpcs value=0}
	{assign var=stvar value=0}
	{assign var=scolspan value=""}
	{assign var=colspan value=""}

	{foreach from=$grn_items item=item key=iid name=fitem}
		{if $item.item_group eq '1' || $item.item_group eq '2'}
			{if $form.is_under_gst && $item.inclusive_tax eq 'yes'}
				{assign var=selling_price value=$item.gst_selling_price}
			{else}
				{assign var=selling_price value=$item.selling_price}
			{/if}
			{if $selling_price != $item.curr_selling_price && $item.po_item_id ne '' && $selling_price ne ''}
				{assign var=have_sec_page value=1}
			{/if}
		{/if}
		{if $item.item_group eq '0' || $item.item_group eq '1' || $item.item_group eq '2'}
			{assign var=row_counter value=$row_counter+1}
			{if $row_counter eq '1'}
				<div class=printarea>
					<table class=small align=right cellpadding=4 cellspacing=0 border=0>
					<tr bgcolor=#cccccc>
						<td align=center><b>Goods Receiving Note</b></td>
					</tr>
					<tr bgcolor=#cccccc>
						<td align=center><b>{$page}</b></td>
					</tr>
					</table>
					<h2>{$branch.description}</h2>
					<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>
					<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
					<tr>
						<td><b>Vendor</b></td><td>{$grr.vendor}</td>
						<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
					</tr>
					<tr>
						<td><b>Document No.</b></td><td><font color=blue>{$grr.doc_no}</font></td>
						<td><b>Document Type.</b></td><td><font color=blue>{$grr.type}</font></td>
					</tr>
					</table>
					<br />
					<h3>Items in {$grr.type}</h3>
					<div style="border:2px solid #000; padding:1px;">
					<table class="box small tb" width="100%" cellpadding="2" cellspacing="0" border="0">
						<tr class="topline botline" bgcolor="#cccccc">
							<th rowspan="2">#</th>
							{if !$smarty.request.newly_added}
								<th rowspan="2">From<br />ISI</th>
							{else}
								{assign var=scolspan value=8}
								{assign var=colspan value=17}
							{/if}
							<th rowspan="2">Return</th>
							<th rowspan="2">ARMS Code/<br />Mcode</th>
							<th rowspan="2">Artno</th>
							<th rowspan="2">Description</th>
							<th rowspan="2">Packing<br />UOM</th>
							<th rowspan="2">Purchase<br />UOM</th>
							<th rowspan="2">Cost<br />Price</th>
							<th colspan="2">{if $grr.type eq 'PO' || !$grr.is_ibt_do}Order{else}Delivery{/if}</th>
							<th colspan="2">Received</th>
							<th colspan="2">Return</th>
							<th rowspan="2">Var<br />(Pcs)</th>
							<th rowspan="2">Total<br />Cost</th>
							<th rowspan="2">Remark</th>
						</tr>
						<tr class="topline botline" bgcolor="#cccccc">
							<th>Ctn</th>
							<th>Pcs</th>
							<th>Ctn</th>
							<th>Pcs</th>
							<th>Ctn</th>
							<th>Pcs</th>
						</tr>
						<tr>
							<td colspan="{$colspan|default:17}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src="ui/pixel.gif" width="1" height="1"></td>
						</tr>
						<tbody id="tbditems">
				{assign var=header value=1}
			{/if}
			{if $item.sku_id ne $curr_sku_id && $curr_sku_id}
				<tr>
					<td colspan="{$colspan|default:18}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src="ui/pixel.gif" width="1" height="1"></td>
				</tr>
				<tr class="topline" height="24" bgcolor="#cccccc">
					<td colspan="{$scolspan|default:9}" align="right"><b>Sub Total</b></td>
					<td align="right">{$stpctn|qty_nf}</td>
					<td align="right">{$stppcs|qty_nf}</td>
					<td align="right">{$stctn|qty_nf}</td>
					<td align="right">{$stpcs|qty_nf}</td>
					<td align="right">{$strctn|qty_nf}</td>
					<td align="right">{$strpcs|qty_nf}</td>
					<td align="right">{$stvar|qty_nf}</td>
					<td align="right">{$stcost|number_format:2}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="{$colspan|default:18}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src="ui/pixel.gif" width="1" height="1"></td>
				</tr>
				{assign var=stpctn value=0}
				{assign var=stppcs value=0}
				{assign var=stctn value=0}
				{assign var=stpcs value=0}
				{assign var=strctn value=0}
				{assign var=strpcs value=0}
				{assign var=stvar value=0}
				{assign var=stcost value=0}
			{/if}
			<tr height="30" bgcolor="{cycle name=r1 values=',#eeeeee'}" class="no_border_bottom {if $item.item_group eq 1}line_strike{/if}">
				<td align="center" width="3%">{$row_counter}.</td>
				{if !$smarty.request.newly_added}
					<td align="center" width="2%">
						{if $item.from_isi}<img src="ui/checked.gif" style="vertical-align:top;">{/if}
					</td>
				{/if}
				<td align="center" width="2%">{if $item.po_item_id eq '0'}{if $item.item_check eq '1'}<img src="ui/checked.gif" style="vertical-align:top;">{/if}{/if}</td>
				<td align="center" width="7%">{$item.sku_item_code}/{if $item.mcode<>''}<br />{$item.mcode|default:"-"}{else}-{/if}</td>
				<td align="center" width="7%">{$item.artno|default:"-"}</td>
				<td width="41%"><div class="crop" style="height:2em">{$item.description}</div></td>
				<td align="center" width="3%">{$item.uom_code}</td>
				<td align="center" width="3%">{$item.po_uom}</td>
				<td align="center" width="3%">{$item.cost|number_format:$config.global_cost_decimal_points}</td>
				<td align="right" width="2%">{$item.po_order_ctn|qty_nf|ifzero:"-"}<br />{if $item.po_foc_ctn}F:{$item.po_foc_ctn|qty_nf}{else}-{/if}</td>
				<td align="right" width="2%">{$item.po_order_pcs|qty_nf|ifzero:"-"}<br />{if $item.po_foc_pcs}F:{$item.po_foc_pcs|qty_nf}{else}-{/if}</td>
				{assign var=po_order_qty value=$item.po_order_ctn*$item.uom_fraction+$item.po_order_pcs}
				{assign var=po_foc_qty value=$item.po_foc_ctn*$item.uom_fraction+$item.po_foc_pcs}
				{assign var=rcv_qty value=$item.ctn*$item.uom_fraction+$item.pcs}
				{assign var=row_ttl_cost value=$item.cost*$rcv_qty}
				<td align="right" width="2%">{$item.ctn|qty_nf|ifzero:"-"}</td>
				<td align="right" width="2%">{$item.pcs|qty_nf|ifzero:"-"}</td>
				<td align="right" width="2%">{$item.return_ctn|qty_nf|ifzero:"-"}</td>
				<td align="right" width="2%">{$item.return_pcs|qty_nf|ifzero:"-"}</td>
				<td align="right" width="2%">
					{assign var=qty_var value=$rcv_qty-$item.return_ctn*$item.uom_fraction-$item.return_pcs-$po_order_qty}
					{if $qty_var > 0}
						{assign var=foc_var value=$qty_var-$po_foc_qty}
						{if $foc_var > 0}
							{assign var=qty_var value=$foc_var}
							{assign var=foc_var value=0}
						{else}
							{assign var=qty_var value=0}
						{/if}
					{else}
						{assign var=foc_var value=$po_foc_qty*-1}
					{/if}
					{$qty_var|qty_nf|ifzero:"-"}<br />
					{if $foc_var}F: {$foc_var|qty_nf}{else}-{/if}
				</td>
				<td align="right" width="3%">{$row_ttl_cost|number_format:2}</td>
				<td width="12%">
					<div class="crop" style="height:2em">
						{$item.po_no} - 
						{if $item.item_group eq '0'}
							Undelivered item
						{elseif $item.item_group eq '1'}
							Matched with {$grr.type} item
						{else}
							Matched with {$grr.type} item's Parent
						{/if}
					</div>
				</td>
				{assign var=tpctn value=`$tpctn+$item.po_ctn`}
				{assign var=tppcs value=`$tppcs+$item.po_pcs`}
				{assign var=tctn value=`$tctn+$item.ctn`}
				{assign var=tpcs value=`$tpcs+$item.pcs`}
				{assign var=trctn value=`$trctn+$item.return_ctn`}
				{assign var=trpcs value=`$trpcs+$item.return_pcs`}
				{assign var=tvar value=`$tvar+$qty_var+$foc_var`}
				{assign var=tcost value=`$tcost+$row_ttl_cost`}
				{assign var=stpctn value=`$stpctn+$item.po_ctn`}
				{assign var=stppcs value=`$stppcs+$item.po_pcs`}
				{assign var=stctn value=`$stctn+$item.ctn`}
				{assign var=stpcs value=`$stpcs+$item.pcs`}
				{assign var=strctn value=`$strctn+$item.return_ctn`}
				{assign var=strpcs value=`$strpcs+$item.return_pcs`}
				{assign var=stvar value=`$stvar+$qty_var+$foc_var`}
				{assign var=stcost value=`$stcost+$row_ttl_cost`}
			</tr>
			{assign var=curr_sku_id value=$item.sku_id}
		{/if}
		{if $smarty.foreach.fitem.last && $row_counter}
				<tr>
					<td colspan="{$colspan|default:18}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src="ui/pixel.gif" width="1" height="1"></td>
				</tr>
				<tr class="topline" height="24" bgcolor="#cccccc">
					<td colspan="{$scolspan|default:9}" align="right"><b>Sub Total</b></td>
					<td align="right">{$stpctn|qty_nf}</td>
					<td align="right">{$stppcs|qty_nf}</td>
					<td align="right">{$stctn|qty_nf}</td>
					<td align="right">{$stpcs|qty_nf}</td>
					<td align="right">{$strctn|qty_nf}</td>
					<td align="right">{$strpcs|qty_nf}</td>
					<td align="right">{$stvar|qty_nf}</td>
					<td align="right">{$stcost|number_format:2}</td>
					<td>&nbsp;</td>
				</tr>
				{assign var=stpctn value=0}
				{assign var=stppcs value=0}
				{assign var=stctn value=0}
				{assign var=stpcs value=0}
				{assign var=strctn value=0}
				{assign var=strpcs value=0}
				{assign var=stvar value=0}
				{assign var=stcost value=0}
				</tbody>
				<tr>
					<td colspan="{$colspan|default:18}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src="ui/pixel.gif" width="1" height="1"></td>
				</tr>
				<tr class="topline" height="24" bgcolor="#cccccc">
					<td colspan="{$scolspan|default:9}" align="right"><b>Total</b></td>
					<td align="right">{$tpctn|qty_nf}</td>
					<td align="right">{$tppcs|qty_nf}</td>
					<td align="right">{$tctn|qty_nf}</td>
					<td align="right">{$tpcs|qty_nf}</td>
					<td align="right">{$trctn|qty_nf}</td>
					<td align="right">{$trpcs|qty_nf}</td>
					<td align="right">{$tvar|qty_nf}</td>
					<td align="right">{$tcost|number_format:2}</td>
					<td>&nbsp;</td>
				</tr>
			</table>
			</div>
			{assign var=have_first_page value=1}
		{/if}
	{/foreach}
{/if}

{assign var=row_counter value=0}
{assign var=tctn value=0}
{assign var=tpcs value=0}
{assign var=ttl_row_cost value=0}
{assign var=ttl_cost value=0}
{assign var=scolspan value=""}
{assign var=colspan value=""}

{if !$div || $div eq '1'}
	{foreach from=$grn_items item=item key=iid name=fitem}
		{if $item.item_group eq '3'}
			{assign var=row_counter value=$row_counter+1}
			{if $row_counter eq '1'}
				{if !$header}
					<div class=printarea>
						<table class=small align=right cellpadding=4 cellspacing=0 border=0>
						<tr bgcolor=#cccccc>
							<td align=center><b>Goods Receiving Note</b></td>
						</tr>
						<tr bgcolor=#cccccc>
							<td align=center><b>{$page}</b></td>
						</tr>
						</table>
						<h2>{$branch.description}</h2>
						<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>
						<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
						<tr>
							<td><b>Vendor</b></td><td>{$grr.vendor}</td>
							<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
						</tr>
						<tr>
							<td><b>Document No.</b></td><td><font color=blue>{$grr.doc_no}</font></td>
							<td><b>Document Type.</b></td><td><font color=blue>{$grr.type}</font></td>
						</tr>
						</table>
						{assign var=header value=1}
				{/if}
				<br />
				{if ($grr.type eq 'PO' || $grr.is_ibt_do) && !$grr.allow_grn_without_po}
					<h3>SKU Return List</h3>
				{elseif $grr.type eq 'PO'|| $grr.is_ibt_do}
					<h3>Items not in {$grr.type}</h3>
				{else}
					<h3>Received items</h3>
				{/if}
				<div style="border:2px solid #000; padding:1px;">
				<table class="box small tb" width="100%" cellpadding="2" cellspacing="0" border="0">
					<tr class="topline botline" bgcolor=#cccccc>
						<th rowspan="2">#</th>
						{assign var=scolspan value=7}
						{assign var=colspan value=11}
						{if !$smarty.request.newly_added}
							<th rowspan="2">From<br />ISI</th>
							{assign var=scolspan value=$scolspan+1}
							{assign var=colspan value=$colspan+1}
						{/if}
						<th rowspan="2">Return</th>
						<th rowspan="2">ARMS Code/<br />Mcode</th>
						<th rowspan="2">Artno</th>
						<th rowspan="2">Description</th>
						<th rowspan="2">Packing<br />UOM</th>
						<th rowspan="2">Cost<br />Price</th>
						{if $form.is_under_gst}
							<th rowspan="2">GST</th>
							{assign var=scolspan value=$scolspan+1}
							{assign var=colspan value=$colspan+1}
						{/if}
						<th colspan="2">Received</th>
						<th rowspan="2">Total<br />Cost</th>
						<th rowspan="2">Remark</th>
					</tr>
					<tr class="topline botline" bgcolor=#ccccc>
						<th>Ctn</th>
						<th>Pcs</th>
					</tr>
					<tr>
						<td colspan="{$colspan|default:12}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
					</tr>
					<tbody id="tbditems">	
			{/if}
			{assign var=tctn value=`$tctn+$item.ctn`}
			{assign var=tpcs value=`$tpcs+$item.pcs`}
			{assign var=rcv_qty value=$item.ctn*$item.uom_fraction+$item.pcs}
			{assign var=ttl_row_cost value=$item.cost*$rcv_qty}
			{assign var=ttl_cost value=$ttl_cost+$ttl_row_cost|round:2}
			<tr height="30" bgcolor="{cycle name=r3 values=',#eeeeee'}" class="no_border_bottom">
				<td align="center" width="3%">{$row_counter}.</td>
				{if !$smarty.request.newly_added}
					<td align="center" width="2%">
						{if $item.from_isi}<img src="ui/checked.gif" style="vertical-align:top;">{/if}
					</td>
				{/if}
				<td align="center" width="2%">{if $item.item_check eq '1'}<img src="ui/checked.gif" style="vertical-align:top;">{/if}</td>
				<td align="center" width="7%">{$item.sku_item_code}{if $item.mcode<>''}<br />{$item.mcode|default:"-"}{/if}</td>
				<td align="center" width="7%">{$item.artno|default:"-"}</td>
				<td width="52%"><div class="crop" style="height:2em">{$item.description}</div></td>
				<td width="3%" align="center">{$item.uom_code}</td>
				<td width="3%" align="right">{$item.cost|number_format:$config.global_cost_decimal_points:".":""|default:"-"}</td>
				{if $form.is_under_gst}
					{assign var=gst_amt value=$item.cost*$item.gst_rate/100}
					{assign var=gst_amt value=$gst_amt|round:$config.global_cost_decimal_points}
					<td align="right" nowrap>
						{$item.gst_code} ({$item.gst_rate|default:'0'}%)<br />
						({$gst_amt|number_format:$config.global_cost_decimal_points})
					</td>
				{/if}
				<td width="3%" align="right">{$item.ctn|qty_nf|default:"-"}</td>
				<td width="3%" align="right">{$item.pcs|qty_nf|default:"-"}</td>
				<td width="4%" align="right">{$ttl_row_cost|number_format:2|default:"-"}</td>
				<td width="11%">&nbsp;</td>
			</tr>
		{/if}
		{if $smarty.foreach.fitem.last && $row_counter}
				</tbody>
				<tr>
					<td colspan="{$colspan|default:12}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
				</tr>
				<tr class="topline" height=24 bgcolor=#cccccc>
					<td colspan="{$scolspan|default:8}" align=right><b>Total</b></td>
					<td align="right">{$tctn|qty_nf}</td>
					<td align="right">{$tpcs|qty_nf}</td>
					<td align="right">{$ttl_cost|number_format:2}</td>
					<td align="right">&nbsp;</td>
				</tr>
			</table>
			</div>
		{/if}
	{/foreach}
{/if}

{if $header && (!$div || $div eq '1')}
	<br />
	<div style="border:2px solid #000; padding:1px;">
	<br />
	<br />
	<table width="50%">
		<tr>
			<td width="50%" valign="bottom" class="small">
				_________________<br />
				Verified By<br />
				Name:
			</td>
		
			<td width="50%" valign="bottom" class="small">
				_________________<br />
				Approved By<br />
				Name:
			</td>
		</tr>
	</table>
	</div>
	</div>
{/if}

{if !$div || $div eq '2'}
	{assign var=row_counter value=0}
	{assign var=tpcs value=0}
	{assign var=ttl_row_cost value=0}
	{assign var=ttl_cost value=0}
	{assign var=header value=0}
	{if $form.non_sku_items}
		{foreach from=$form.non_sku_items.code key=sku_code item=qty name=fitem}
			{assign var=n value=$smarty.foreach.fitem.iteration-1}
			{assign var=row_counter value=$row_counter+1}
			{assign var=tpcs value=`$tpcs+$form.non_sku_items.qty.$n`}
			{assign var=ttl_row_cost value=$form.non_sku_items.cost.$n*$form.non_sku_items.qty.$n}
			{assign var=ttl_cost value=$ttl_cost+$ttl_row_cost|round:2}
			{if $row_counter eq '1'}
				<div class=printarea>
					<table class=small align=right cellpadding=4 cellspacing=0 border=0>
						<tr bgcolor=#cccccc>
							<td align=center><b>Goods Receiving Note</b></td>
						</tr>
						<tr bgcolor=#cccccc>
							<td align=center><b>{$page}</b></td>
						</tr>
					</table>
					<h2>{$branch.description}</h2>
					<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>
					<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
					<tr>
						<td><b>Vendor</b></td><td>{$grr.vendor}</td>
						<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
					</tr>
					<tr>
						<td><b>Document No.</b></td><td><font color=blue>{$grr.doc_no}</font></td>
						<td><b>Document Type.</b></td><td><font color=blue>{$grr.type}</font></td>
					</tr>
					</table>
					<br />
					<h3>SKU not in ARMS</h3>
					<div style="border:2px solid #000; padding:1px;">
					<table class="box small tb" width="100%" cellpadding="2" cellspacing="0" border="0">
						<tr class="topline botline" bgcolor=#cccccc>
							<th>#</th>
							<th>Return</th>
							<th>Code</th>
							<th>Description</th>
							<th>Cost<br />Price</th>
							<th>Rcv Qty<br />(Pcs)</th>
							<th>Total<br />Cost</th>
							<th>Remark</th>
						</tr>
						<tr>
							<td colspan="8" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
						</tr>
						<tbody id="tbditems">
				{assign var=header value=1}
			{/if}
			<tr height=30 bgcolor="{cycle name=r4 values=",#eeeeee"}" class="no_border_bottom">
				<td width="3%" align="center">{$row_counter}.</td>
				<td width="2%" align="center">{if $form.non_sku_items.i_c.$n}<img src="ui/checked.gif" style="vertical-align:top;">{/if}</td>
				<td width="7%" align="center">{$form.non_sku_items.code.$n}</td>
				<td width="67%">{$form.non_sku_items.description.$n}</td>
				<td width="3%" align="right">{$form.non_sku_items.cost.$n|number_format:$config.global_cost_decimal_points|default:"-"}</td>
				<td width="3%" align="right">{$form.non_sku_items.qty.$n|qty_nf|default:"-"}</td>
				<td width="4%" align="right">{$ttl_row_cost|number_format:2|default:"-"}</td>
				<td width="11%" align="right">&nbsp;</td>
			</tr>
			{if $smarty.foreach.fitem.last}
					</tbody>
					<tr>
						<td colspan="8" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
					</tr>
					<tr class="topline" height="24" bgcolor="#cccccc">
						<td colspan="5" align="right"><b>Total</b></td>
						<td align="right">{$tpcs|qty_nf}</td>
						<td align="right">{$ttl_cost|number_format:2}</td>
						<td align="right">&nbsp;</td>
					</tr>
				</table>
				</div>
			{/if}
		{/foreach}
	{/if}
{/if}

{if $header && (!$div || $div eq '2')}
	<br />
	<div style="border:2px solid #000; padding:1px;">
	<br />
	<br />
	<table width="50%">
		<tr>
			<td width="50%" valign="bottom" class="small">
				_________________<br />
				Verified By<br />
				Name:
			</td>
		
			<td width="50%" valign="bottom" class="small">
				_________________<br />
				Approved By<br />
				Name:
			</td>
		</tr>
	</table>
	</div>
	</div>
{/if}

{if ($grr.type eq 'PO'|| $grr.is_ibt_do) && (!$div || $div eq '3')}
	{assign var=row_counter value=0}
	{assign var=header value=0}

	{foreach name=i from=$grn_items item=item key=iid name=gitem}
		{if $item.item_group eq '1' || $item.item_group eq '2'}
			{if $form.is_under_gst && $item.inclusive_tax eq 'yes'}
				{assign var=selling_price value=$item.gst_selling_price}
			{else}
				{assign var=selling_price value=$item.selling_price}
			{/if}
			{if $selling_price != $item.curr_selling_price && $item.po_item_id ne '' && $selling_price ne ''}
				{assign var=row_counter value=$row_counter+1}
				{if $row_counter == 1}
					<div class=printarea>
						<table class=small align=right cellpadding=4 cellspacing=0 border=0>
							<tr bgcolor=#cccccc>
								<td align=center><b>Goods Receiving Note</b></td>
							</tr>
							<tr bgcolor=#cccccc>
								<td align=center><b>{$page}</b></td>
							</tr>
						</table>
						<h2>{$branch.description}</h2>
						<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>
						<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
						<tr>
							<td><b>Vendor</b></td><td>{$grr.vendor}</td>
							<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
						</tr>
						<tr>
							<td><b>Document No.</b></td><td><font color=blue>{$grr.doc_no}</font></td>
							<td><b>Document Type.</b></td><td><font color=blue>{$grr.type}</font></td>
						</tr>
						</table>
						<br />
						<h3>{$grr.type} Suggested Selling Price</h3>
						<div style="border:2px solid #000; padding:1px;">
						<table class="box small tb" width="100%" cellpadding="2" cellspacing="0" border="0">
							<tr class="topline botline" bgcolor="#cccccc">
								<th>#</th>
								<th>ARMS Code/<br />Mcode</th>
								<th>Artno</th>
								<!--<th rowspan=2>Mcode</th>-->
								<th>Description</th>
								<th>{$grr.type} No</th>
								<th>{$grr.type} Date</th>
								<th>Packing<br />UOM</th>
								<th>Cost<br />Price</th>
								<th>Current<br />Selling<br />Price</th>
								<th>Suggested<br />Selling<br />Price</th>
								<th>Remark</th>
							</tr>
							<tr>
								<td colspan="11" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
							</tr>
							<tbody id="tbditems">
					{assign var=header value=1}
				{/if}
				<tr height=30 bgcolor="{cycle name=r5 values=",#eeeeee"}" class="no_border_bottom">
					<td align="center" width="3%">{$row_counter}.</td>
					<td align="center" width="7%">{$item.sku_item_code}{if $item.mcode<>''}<br />{$item.mcode|default:"-"}{/if}</td>
					<td align="center" width="7%">{$item.artno|default:"-"}</td>
					<td width="54%"><div class="crop" style="height:2em">{$item.description}</div></td>
					<td width="3%">{$item.po_no}</td>
					<td width="3%">{$item.po_date}</td>
					<td align="center" width="3%">{$item.uom_code}</td>
					<td align="right" width="3%">{$item.cost|number_format:$config.global_cost_decimal_points:".":""}</td>
					<td align="right" width="3%">{$item.curr_selling_price|number_format:2:".":""}</td>
					<td align="right" width="3%">{$selling_price|number_format:2:".":""}</td>
					<td width="11%">{$item.reason|default:'&nbsp;'}</td>
				</tr>
			{/if}
		{/if}
		{if $smarty.foreach.gitem.last && $row_counter}
				</tbody>
			</table>
			</div>
			{assign var=have_sec_page value=1}
		{/if}
	{/foreach}
	{if $header && (!$div || $div eq '3')}
		<br />
		<div style="border:2px solid #000; padding:1px;">
		<br />
		<br />
		<table width="50%">
			<tr>
				<td width="50%" valign="bottom" class="small">
					_________________<br />
					Verified By<br />
					Name:
				</td>
			
				<td width="50%" valign="bottom" class="small">
					_________________<br />
					Approved By<br />
					Name:
				</td>
			</tr>
		</table>
		</div>
		</div>
	{/if}
{/if}
