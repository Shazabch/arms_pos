{*
7/15/2011 1:42:42 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

7/19/2011 3:04:20 PM Justin
- Fixed the Received items that do not show all received qty due to the Ctn did not sum up with pcs.

7/26/2011 6:18:12 PM Justin
- Enhanced the report to have ctn and return ctn fields.
- Removed all the "Qty (Pcs)" which it is being replaced by Pcs and Ctn columns.

7/27/2011 11:54:42 AM Justin
- Fixed the PO Variance does not calculate properly.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

2/28/2012 11:31:43 AM Justin
- Added new ability to show DO as PO format when it is IBT DO.

7/3/2012 4:45:12 PM Justin
- Fixed bug of report always showing "Not Approved" even the document has fully approved.

7/5/2012 4:33:34 PM Justin
- Enhanced to show between PO and DO at the header.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

12/11/2013 2:13 PM Justin
- Enhanced to show offline ID.

11/18/2014 5:26 PM Justin
- Enhanced to show GST column and calculation.

3/25/2015 2:33 PM Justin
- Enhanced to have between nett selling price or GST selling price while doing current vs suggested selling price.

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

3/14/20156 1:23 PM Qiu Ying
- Fix wrong calculation in total 

11/2/2016 4:45 PM Andy
- Fixed report error when item more than 1 page.
- Fixed to only print sku not in arms at last page.
- Fixed footer duplicate.

1/12/2017 4:14 PM Andy
- Enhanced to use branch_is_under_gst to check gst selling price.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

11/22/2018 10:26 AM Justin
- Enhanced to show Old Code instead of ARMS Code when config is turned on.

12/28/2018 1:24 PM Justin
- Enhanced to print barcode on top of the document number.

5/21/2019 5:12 PM William
- Enhance "GRN" word to use report_prefix.
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
var doc_no = '{$branch.report_prefix}{$smarty.request.id|string_format:"%05d"}';
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
	{assign var=trctn value=0}
	{assign var=trpcs value=0}
	{assign var=tpcs value=0}
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
			{if $form.branch_is_under_gst && $item.inclusive_tax eq 'yes'}
				{assign var=selling_price value=$item.gst_selling_price}
			{else}
				{assign var=selling_price value=$item.selling_price}
			{/if}
			{if $selling_price != $item.curr_selling_price && $item.po_item_id ne '' && $selling_price ne ''}
				{assign var=have_sec_page value=1}
			{/if}
		{/if}
		{if $item.item_group eq '0' || $item.item_group eq '1' || $item.item_group eq '2'}
			{if !$page_item_info.$iid.not_item}
				{assign var=row_counter value=$row_counter+1}
			{/if}
			{if $row_counter eq '1' && !$header}
				<div class=printarea>
					<table class=small align=right cellpadding=4 cellspacing=0 border=0>
					<tr bgcolor=#cccccc>
						<td align=center>
							<b>Goods Receiving Note<br />
							{if !$grn.authorized}(Not Approved)<br />{/if}
							</b>
							{if $config.print_document_barcode}
								<span class="barcode3of9" style="padding:0;">
									*{$grn.id|string_format:"%05d"}*
								</span>
							{/if}
							
							<b>
							<div {if $config.print_document_barcode}style="margin-top:-5px;"{/if}>
								{$branch.report_prefix}{$grn.id|string_format:"%05d"}
							</div>
							
							{if $grn.offline_id}
								<br />(Offline ID:#{$grn.offline_id|string_format:"%05d"})
							{/if}
							</b>
						</td>
					</tr>
					<tr bgcolor=#cccccc>
						<td align=center><b>{$page}</b></td>
					</tr>
					</table>
					<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
					<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>
	
					<br />
					<h3>Items in {$grr.type}</h3>
					<div style="border:2px solid #000; padding:1px;">
					<table class="box small tb" width="100%" cellpadding="2" cellspacing="0" border="0">
						<tr class="topline botline" bgcolor="#cccccc">
							<th rowspan="2">#</th>
							{if !$smarty.request.newly_added}
								<th rowspan="2">From<br />ISI</th>
							{else}
								{assign var=scolspan value=7}
								{assign var=colspan value=15}
							{/if}
							<th rowspan="2">Return</th>
							<th rowspan="2">
								{if $config.replace_docs_arms_code_with_link_code}
									{$config.link_code_name|default:'Old Code'}
								{else}
									ARMS Code
								{/if}/<br />
								Mcode
							</th>
							<th rowspan="2">Artno</th>
							<th rowspan="2">Description</th>
							<th rowspan="2">Packing<br />UOM</th>
							<th rowspan="2">Purchase<br />UOM</th>
							<th colspan="2">{if $grr.type eq 'PO' || !$grr.is_ibt_do}Order{else}Delivery{/if}</th>
							<th colspan="2">Received</th>
							<th colspan="2">Return</th>
							<th rowspan="2">Var<br />(Pcs)</th>
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
							<td colspan="{$colspan|default:16}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src="ui/pixel.gif" width="1" height="1"></td>
						</tr>
						<tbody id="tbditems">
				{assign var=header value=1}
			{/if}
			{if $item.sku_id ne $curr_sku_id && $curr_sku_id}
				<tr>
					<td colspan="{$colspan|default:16}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src="ui/pixel.gif" width="1" height="1"></td>
				</tr>
				<tr class="topline" height="24" bgcolor="#cccccc">
					<td colspan="{$scolspan|default:8}" align="right"><b>Sub Total</b></td>
					<td align="right">{$stpctn|qty_nf}</td>
					<td align="right">{$stppcs|qty_nf}</td>
					<td align="right">{$stctn|qty_nf}</td>
					<td align="right">{$stpcs|qty_nf}</td>
					<td align="right">{$strctn|qty_nf}</td>
					<td align="right">{$strpcs|qty_nf}</td>
					<td align="right">{$stvar|qty_nf}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="{$colspan|default:16}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src="ui/pixel.gif" width="1" height="1"></td>
				</tr>
				{assign var=stpctn value=0}
				{assign var=stppcs value=0}
				{assign var=stctn value=0}
				{assign var=stpcs value=0}
				{assign var=strctn value=0}
				{assign var=strpcs value=0}
				{assign var=stvar value=0}
			{/if}
			<tr height="30" bgcolor="{cycle name=r1 values=',#eeeeee'}" class="no_border_bottom {if $item.item_group eq 1}line_strike{/if}">
				{if !$page_item_info.$iid.not_item}
					<td align="center" width="3%">{$row_counter+$start_counter}.</td>
					{if !$smarty.request.newly_added}
						<td align="center" width="2%">
							{if $item.from_isi}<img src="ui/checked.gif" style="vertical-align:top;">{/if}
						</td>
					{/if}
					<td align="center" width="2%">{if $item.po_item_id eq '0'}{if $item.item_check eq '1'}<img src="ui/checked.gif" style="vertical-align:top;">{/if}{/if}</td>
					<td width="7%">
						{if $config.replace_docs_arms_code_with_link_code}
							{$item.link_code|default:'&nbsp;'}
						{else}
							{$item.sku_item_code}
						{/if}
						<br />{if $item.mcode<>''}{$item.mcode|default:"-"}{else}-{/if}
					</td>
					<td align="center" width="7%">{$item.artno|default:"-"}</td>
				{else}
					<td></td>
					{if !$smarty.request.newly_added}
						<td></td>
					{/if}
					<td></td><td></td><td></td>
				{/if}
				<td width="48%"><div class="crop" style="height:2em">{$item.description}</div></td>
				{if !$page_item_info.$iid.not_item}
					<td align="center" width="3%">{$item.uom_code}</td>
					<td align="center" width="3%">{$item.po_uom}</td>
					<td align="right" width="2%">{$item.po_order_ctn|ifzero:"-"}<br />{if $item.po_foc_ctn}F:{$item.po_foc_ctn|qty_nf}{else}-{/if}</td>
					<td align="right" width="2%">{$item.po_order_pcs|ifzero:"-"}<br />{if $item.po_foc_pcs}F:{$item.po_foc_pcs|qty_nf}{else}-{/if}</td>
					{assign var=po_order_qty value=$item.po_order_ctn*$item.uom_fraction+$item.po_order_pcs}
					{assign var=po_foc_qty value=$item.po_foc_ctn*$item.uom_fraction+$item.po_foc_pcs}
					{assign var=rcv_qty value=$item.ctn*$item.uom_fraction+$item.pcs}
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
					{assign var=stpctn value=`$stpctn+$item.po_ctn`}
					{assign var=stppcs value=`$stppcs+$item.po_pcs`}
					{assign var=stctn value=`$stctn+$item.ctn`}
					{assign var=stpcs value=`$stpcs+$item.pcs`}
					{assign var=strctn value=`$strctn+$item.return_ctn`}
					{assign var=strpcs value=`$strpcs+$item.return_pcs`}
					{assign var=stvar value=`$stvar+$qty_var+$foc_var`}
				{else}
					<td></td><td></td><td></td>
					<td></td><td></td><td></td>
					<td></td><td></td><td></td><td></td>
				{/if}
			</tr>
			{assign var=curr_sku_id value=$item.sku_id}
		{/if}
		{if $smarty.foreach.fitem.last && $row_counter}
				<tr>
					<td colspan="{$colspan|default:16}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src="ui/pixel.gif" width="1" height="1"></td>
				</tr>
				<tr class="topline" height="24" bgcolor="#cccccc">
					<td colspan="{$scolspan|default:8}" align="right"><b>Sub Total</b></td>
					<td align="right">{$stpctn|qty_nf}</td>
					<td align="right">{$stppcs|qty_nf}</td>
					<td align="right">{$stctn|qty_nf}</td>
					<td align="right">{$stpcs|qty_nf}</td>
					<td align="right">{$strctn|qty_nf}</td>
					<td align="right">{$strpcs|qty_nf}</td>
					<td align="right">{$stvar|qty_nf}</td>
					<td>&nbsp;</td>
				</tr>
				{assign var=stpctn value=0}
				{assign var=stppcs value=0}
				{assign var=stctn value=0}
				{assign var=stpcs value=0}
				{assign var=strctn value=0}
				{assign var=strpcs value=0}
				{assign var=stvar value=0}
				</tbody>
				<tr>
					<td colspan="{$colspan|default:16}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src="ui/pixel.gif" width="1" height="1"></td>
				</tr>
				<tr class="topline" height="24" bgcolor="#cccccc">
					<td colspan="{$scolspan|default:8}" align="right"><b>Total</b></td>
					<td align="right">{$tpctn|qty_nf}</td>
					<td align="right">{$tppcs|qty_nf}</td>
					<td align="right">{$tctn|qty_nf}</td>
					<td align="right">{$tpcs|qty_nf}</td>
					<td align="right">{$trctn|qty_nf}</td>
					<td align="right">{$trpcs|qty_nf}</td>
					<td align="right">{$tvar|qty_nf}</td>
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
{assign var=scolspan value=""}
{assign var=colspan value=""}
{assign var=skip_row_header2 value=0}
{assign var=header value=0}
{if !$div || $div eq '1'}
	{foreach from=$grn_items item=item key=iid name=fitem}
		{if $item.item_group eq '3'}
			{if !$page_item_info.$iid.not_item}
				{assign var=row_counter value=$row_counter+1}
			{/if}
			{if $row_counter eq '1' && !$skip_row_header2}
				{if !$header}
					<div class=printarea>
						<table class=small align=right cellpadding=4 cellspacing=0 border=0>
						<tr bgcolor=#cccccc>
							<td align=center>
								<b>Goods Receiving Note<br />
								{if !$grn.authorized}(Not Approved)<br />{/if}
								</b>
								{if $config.print_document_barcode}
									<span class="barcode3of9" style="padding:0;">
										*{$grn.id|string_format:"%05d"}*
									</span>
								{/if}
								
								<b>
								<div {if $config.print_document_barcode}style="margin-top:-5px;"{/if}>
									{$branch.report_prefix}{$grn.id|string_format:"%05d"}
								</div>
								
								{if $grn.offline_id}
									<br />(Offline ID:#{$grn.offline_id|string_format:"%05d"})
								{/if}
								</b>
							</td>
						</tr>
						<tr bgcolor=#cccccc>
							<td align=center><b>{$page}</b></td>
						</tr>
						</table>
						<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
						<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>
						{assign var=header value=1}
				{/if}
				<br />
				{if ($grr.type eq 'PO' || $grr.is_ibt_do) && !$grr.allow_grn_without_po}
					<h3>SKU Return List</h3>
				{elseif $grr.type eq 'PO' || $grr.is_ibt_do}
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
						<th rowspan="2">
							{if $config.replace_docs_arms_code_with_link_code}
								{$config.link_code_name|default:'Old Code'}
							{else}
								ARMS Code
							{/if}/<br />
							Mcode
						</th>
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
						<th rowspan="2">Remark</th>
					</tr>
					<tr class="topline botline" bgcolor=#ccccc>
						<th>Ctn</th>
						<th>Pcs</th>
					</tr>
					<tr>
						<td colspan="{$colspan|default:11}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
					</tr>
					<tbody id="tbditems">	
					{assign var=skip_row_header2 value=1}
			{/if}
			{assign var=rcv_qty value=$item.ctn*$item.uom_fraction+$item.pcs}
			{assign var=tctn value=`$tctn+$item.ctn`}
			{assign var=tpcs value=`$tpcs+$item.pcs`}
			<tr height="30" bgcolor="{cycle name=r3 values=',#eeeeee'}" class="no_border_bottom">
				{if !$page_item_info.$iid.not_item}
					<td align="center" width="3%">{$row_counter+$start_counter}.</td>
					{if !$smarty.request.newly_added}
						<td align="center" width="2%">
							{if $item.from_isi}<img src="ui/checked.gif" style="vertical-align:top;">{/if}
						</td>
					{/if}
					<td align="center" width="2%">{if $item.item_check eq '1'}<img src="ui/checked.gif" style="vertical-align:top;">{/if}</td>
					<td width="7%">
						{if $config.replace_docs_arms_code_with_link_code}
							{$item.link_code|default:'&nbsp;'}
						{else}
							{$item.sku_item_code}
						{/if}
						<br />{if $item.mcode<>''}{$item.mcode|default:"-"}{else}-{/if}
					</td>
					<td align="center" width="7%">{$item.artno|default:"-"}</td>
				{else}
					<td>&nbsp;</td>
					{if !$smarty.request.newly_added}
						<td>&nbsp;</td>
					{/if}
					<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
				{/if}
				<td width="59%"><div class="crop" style="height:2em">{$item.description}</div></td>
				{if !$page_item_info.$iid.not_item}
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
					<td width="11%">&nbsp;</td>
				{else}
					<td>&nbsp;</td><td>&nbsp;</td>
					{if $form.is_under_gst}
						<td>&nbsp;</td>
					{/if}
					<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
				{/if}
			</tr>
		{/if}
		{if $smarty.foreach.fitem.last && $row_counter}
				</tbody>
				<tr>
					<td colspan="{$colspan|default:11}" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
				</tr>
				<tr class="topline" height=24 bgcolor=#cccccc>
					<td colspan="{$scolspan|default:8}" align=right><b>Total</b></td>
					<td align="right">{$tctn|qty_nf}</td>
					<td align="right">{$tpcs|qty_nf}</td>
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

{if $is_lastpage && (!$div || $div eq '2')}
	{assign var=row_counter value=0}
	{assign var=tpcs value=0}
	{assign var=header value=0}
	
	{if $form.non_sku_items}
		{foreach from=$form.non_sku_items.code key=sku_code item=qty name=fitem}
			{assign var=n value=$smarty.foreach.fitem.iteration-1}
			{assign var=row_counter value=$row_counter+1}
			{assign var=tpcs value=`$tpcs+$form.non_sku_items.qty.$n`}
			{if $row_counter eq '1'}
				<div class="printarea">
					<table class=small align=right cellpadding=4 cellspacing=0 border=0>
						<tr bgcolor=#cccccc>
							<td align=center>
								<b>Goods Receiving Note<br />
								{if !$grn.authorized}(Not Approved)<br />{/if}
								</b>
								{if $config.print_document_barcode}
									<span class="barcode3of9" style="padding:0;">
										*{$grn.id|string_format:"%05d"}*
									</span>
								{/if}
								
								<b>
								<div {if $config.print_document_barcode}style="margin-top:-5px;"{/if}>
									{$branch.report_prefix}{$grn.id|string_format:"%05d"}
								</div>
								
								{if $grn.offline_id}
									<br />(Offline ID:#{$grn.offline_id|string_format:"%05d"})
								{/if}
								</b>
							</td>
						</tr>
						<tr bgcolor=#cccccc>
							<td align=center><b>{$page}</b></td>
						</tr>
					</table>
					<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
					<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>
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
							<th>Remark</th>
						</tr>
						<tr>
							<td colspan="7" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
						</tr>
						<tbody id="tbditems">
				{assign var=header value=1}
			{/if}
			<tr height=30 bgcolor="{cycle name=r4 values=",#eeeeee"}" class="no_border_bottom">
				<td width="3%" align="center">{$row_counter}.</td>
				<td width="2%" align="center">{if $form.non_sku_items.i_c.$n}<img src="ui/checked.gif" style="vertical-align:top;">{/if}</td>
				<td width="7%" align="center">{$form.non_sku_items.code.$n}</td>
				<td width="71%">{$form.non_sku_items.description.$n}</td>
				<td width="3%" align="right">{$form.non_sku_items.cost.$n|number_format:$config.global_cost_decimal_points|default:"-"}</td>
				<td width="3%" align="right">{$form.non_sku_items.qty.$n|qty_nf|default:"-"}</td>
				<td width="11%" align="right">&nbsp;</td>
			</tr>
			{if $smarty.foreach.fitem.last}
					</tbody>
					<tr>
						<td colspan="7" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
					</tr>
					<tr class="topline" height="24" bgcolor="#cccccc">
						<td colspan="5" align="right"><b>Total</b></td>
						<td align="right">{$tpcs|qty_nf}</td>
						<td align="right">&nbsp;</td>
					</tr>
				</table>
				</div>
			{/if}
		{/foreach}
		
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
{/if}



{if ($grr.type eq 'PO' || $grr.is_ibt_do) && (!$div || $div eq '3')}
	{assign var=row_counter value=0}
	{assign var=header value=0}

	{foreach name=i from=$grn_items item=item key=iid name=gitem}
		{if $item.item_group eq '1' || $item.item_group eq '2'}
			{if $form.branch_is_under_gst && $item.inclusive_tax eq 'yes'}
				{assign var=selling_price value=$item.gst_selling_price}
			{else}
				{assign var=selling_price value=$item.selling_price}
			{/if}
			{if $selling_price != $item.curr_selling_price && $item.po_item_id ne '' && $selling_price ne ''}
				{if !$page_item_info.$iid.not_item}
					{assign var=row_counter value=$row_counter+1}
				{/if}
				{if $row_counter == 1 && !$skip_row_header1}
					<div class=printarea>
						<table class=small align=right cellpadding=4 cellspacing=0 border=0>
							<tr bgcolor=#cccccc>
								<td align=center>
									<b>Goods Receiving Note<br />
									{if !$grn.authorized}(Not Approved)<br />{/if}
									</b>
									{if $config.print_document_barcode}
										<span class="barcode3of9" style="padding:0;">
											*{$grn.id|string_format:"%05d"}*
										</span>
									{/if}
									
									<div {if $config.print_document_barcode}style="margin-top:-5px;"{/if}>
										{$branch.report_prefix}{$grn.id|string_format:"%05d"}
									</div>
									<b>
									{if $grn.offline_id}
										<br />(Offline ID:#{$grn.offline_id|string_format:"%05d"})
									{/if}
									</b>
								</td>
							</tr>
							<tr bgcolor=#cccccc>
								<td align=center><b>{$page}</b></td>
							</tr>
						</table>
						<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
						<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>
						<br />
						<h3>{$grr.type} Suggested Selling Price</h3>
						<div style="border:2px solid #000; padding:1px;">
						<table class="box small tb" width="100%" cellpadding="2" cellspacing="0" border="0">
							<tr class="topline botline" bgcolor="#cccccc">
								<th>#</th>
								<th>
									{if $config.replace_docs_arms_code_with_link_code}
										{$config.link_code_name|default:'Old Code'}
									{else}
										ARMS Code
									{/if}/<br />
									Mcode
								</th>
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
					{assign var=skip_row_header1 value=1}
				{/if}
				<tr height=30 bgcolor="{cycle name=r5 values=",#eeeeee"}" class="no_border_bottom">
					{if !$page_item_info.$iid.not_item}
						<td align="center" width="3%">{$row_counter}.</td>
						<td width="7%">
							{if $config.replace_docs_arms_code_with_link_code}
								{$item.link_code|default:'&nbsp;'}
							{else}
								{$item.sku_item_code}
							{/if}
							<br />{if $item.mcode<>''}{$item.mcode|default:"-"}{else}-{/if}
						</td>
						<td align="center" width="7%">{$item.artno|default:"-"}</td>
					{else}
						<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
					{/if}
					<td width="54%"><div class="crop" style="height:2em">{$item.description}</div></td>
					{if !$page_item_info.$iid.not_item}
						<td width="3%">{$item.po_no}</td>
						<td width="3%">{$item.po_date}</td>
						<td align="center" width="3%">{$item.uom_code}</td>
						<td align="right" width="3%">{$item.cost|number_format:$config.global_cost_decimal_points:".":""}</td>
						<td align="right" width="3%">{$item.curr_selling_price|number_format:3:".":""}</td>
						<td align="right" width="3%">{$selling_price|number_format:3:".":""}</td>
						<td width="11%">{$item.reason|default:'&nbsp;'}</td>
					{else}
						<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
						<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
						<td>&nbsp;</td>
					{/if}
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
