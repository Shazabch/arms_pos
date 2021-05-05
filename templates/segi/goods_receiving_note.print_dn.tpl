{*
7/25/2012 2:58 PM Justin
- Added "Account ID" info and available when config is found.
- Added Vendor Code info.

8/24/2012 10:22 AM Justin
- Added to show document type, number, partial delivery, branch and related invoice information.

9/24/2012 10:46 AM Justin
- Added to show remark at last page.
- Enhanced to print another page for vendor copy.

1/21/2013 5:06 PM Justin
- Removed current note and replaced with new notes given by customers.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing
*}

{if !$skip_header}
{include file='header.print.tpl'}

<style>
{if $config.gra_printing_no_item_line}
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

<!-- print sheet for company -->
<div class=printarea>
<table width=100% cellpadding=4 cellspacing=0 border=0>
<tr>
<td width=25% align=left nowrap><h4>{$branch.description}</td>
<td width=50% align=center nowrap><h4>DEBIT NOTE
{*if $vendor_copy}<div class=small>(Vendor Copy)</div>{/if*}
{if $copy_type ne 'vendor_copy'}
	{assign var=cols_add value=0}
{else}
	{if $form.sku_type eq 'CONSIGN'}
		{assign var=cols_add value=-1}
	{else}
		{assign var=cols_add value=0}
	{/if}
{/if}
</td>
<td width=25% align=right><h4>DN: {$branch.branch_code}{$grn.id|string_format:"%05d"}{if $page}<br>{$page}{/if}</h4></td>
</tr>
</table>

<div class=small style="padding-bottom:10px">
{$branch.address|nl2br}
<br>
Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if}
</div>

<table width=100% border=0 cellspacing=0 cellpadding=4 class="tb">
<tr>
	<td><b>Vendor</b></td>
	<td colspan="3">{$grr.vendor_code}{if $grr.account_id} - {$grr.account_id}{/if} - {$grr.vendor|default:'&nbsp;'}</td>
	<td><b>Date Added</b></td>
	<td colspan="3">{$grr.added|date_format:"`$config.dat_format` %I:%M%p"}</td>
</tr>
<tr>
	<td><b>Department</b></td>
	<td colspan="3">{$grn.department|default:'&nbsp;'}</td>
	<td><b>Printed By</b></td>
	<td colspan="3">{$sessioninfo.u|default:'&nbsp;'}</td>
</tr>
<tr>
	<td><b>GRR No.</b></td>
	<td>GRR{$grn.grr_id|string_format:"%05d"}/{$grn.grr_item_id}</td>
	<td><b>GRR Date</b></td>
	<td>{$grr.added|date_format:"%d/%m/%Y"}</td>
	<td nowrap><b>GRR Amount(RM)</b></td>
	<td align="right">{if $grr.grr_amount}{$grr.grr_amount|number_format:2}{/if}</td>
</tr>
<tr>
	<td><b>Lorry No</b></td>
	<td>{$grr.transport|default:'&nbsp;'}</td>
	<td><b>Total Ctn / Pcs</b></td>
	<td align="right">{$grr.ctn|default:'0'} / {$grr.pcs|default:'0'}</td>
	<td><b>Account Adjustment</b></td>
	<td align="right">{$grn.buyer_adjustment|number_format:2|default:'&nbsp;'}</td>
</tr>
<tr>
	<td><b>Document Type.</b></td><td><font color=blue>{$grr.type}</font></td>
	<td><b>Document No.</b></td><td><font color=blue>{$grr.doc_no}</font></td>
	<td><b>Branch</b></td><td>{$grn.branch_code}</td>
</tr>
{if $grr.type eq 'PO'}
<tr>
	{if $config.grn_summary_show_related_invoice && $grr.type eq 'PO'}
	<td><b>Related Invoice</b></td>
	<td>{$grr.related_invoice}</td>
	{else}
	<td colspan="2">&nbsp;</td>
	{/if}
	{if $grr.type eq 'PO'}
	<td><b>Partial Delivery</b></td>
	<td colspan="3">{if $config.use_grn_future}{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}{else}{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}{/if}</td>
	{else}
	<td colspan="2">&nbsp;</td>
	<td colspan="2">&nbsp;</td>
	{/if}
</tr>
{/if}
</table>

<br>
{if $items && !$new}
<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">
<tr  bgcolor=#cccccc>
	<th>&nbsp;</th>
	<th>ARMS Code/<br>MCode</th>
	<th>Article No{if $config.link_code_name}/<br>{$config.link_code_name}{/if}</th>
	<th width="40%">Description</th>
	<th>Cost Price<br />(Original)</th>
	<th>Cost Price<br />(Acc. Adjusted)</th>
</tr>
{assign var=t_page value=0}
{assign var=ttl_qty value=0}
{assign var=ttl_amt value=0}
{foreach from=$items key=r item=item name=i}
	<!--{$t_page++}-->

	<tr  id="tbrow_{$item.id}" bgcolor="{cycle values="#eeeeee,"}" class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_last_page}td_btm_got_line{/if}">
		{if !$page_item_info.$r.not_item}
			<td align=right>
			<!--{$line_no++}-->
			{$start_counter+$smarty.foreach.i.iteration}.
			</td>
			<td>{$item.sku_item_code}<br>{$item.mcode}</td>
			<!--td>{$item.mcode|default:"&nbsp;"}</td-->
			
			<td>{$item.artno|default:"&nbsp;"}{if $config.link_code_name}<br>{$item.link_code}{/if}</td>
			{if $config.link_code_name}
			<!--td>{$item.link_code|default:"&nbsp;"}</td-->
			{/if}
		{else}
			<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
		{/if}
		<td>{$item.description}</td>
		{if !$page_item_info.$r.not_item}
			<td align=right>{$item.cost|number_format:$config.global_cost_decimal_points}</td>
			<td align=right>{$item.acc_cost|number_format:$config.global_cost_decimal_points}</td>
		{else}
			<td>&nbsp;</td><td>&nbsp;</td>
		{/if}
	</tr>
{/foreach}

{repeat s=$t_page+1 e=$PAGE_SIZE name=rr}
	<tr height=30 class="no_border_bottom">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
{/repeat}

{*if $show_total and $smarty.request.a eq 'print'}
	<tr class="total_row">
		<th colspan={$cols+$cols_add} align=right class="total_row">Total</th>
		<td align=right class="total_row">{$ttl_qty|qty_nf}</td>
		<td align=right class="total_row">{$ttl_amt|number_format:2}</td>
	</tr>
	{if $is_last_page}
		<tr class="total_row">
			<th colspan={$cols+$cols_add} align=right class="total_row">Grand Total</th>
			<td align=right class="total_row">{$total_qty|qty_nf}</td>
			<td align=right class="total_row">{$total_amt|number_format:2}</td>
		</tr>
	{/if}
{else}
    <tr class="total_row">
		<td colspan="{$cols+2}">&nbsp;</td>
	</tr>
{/if*}
</table>
{/if}

<br />
<b>Note</b>
<div style="border:1px solid #000;padding:5px;height:30px;">
	1. We will pay based on the price in our PO and the quantity in our GRN (Goods Received Notes).
	<br />
	2. We will not perform any statement of account reconciliation
</div>

{if $is_last_page}
	<br />
	<b>Remark</b>
	<div style="border:1px solid #000;padding:5px;height:20px;">
	{$grn.acc_action|default:"-"}
	</div>

	<br />
	<div style="border:2px solid #000; padding:1px;">
	<br />
	<br />
	<table width="70%">
		<tr>
			<td width="30%" valign="bottom" class="small">
				_________________<br />
				Issued By<br />
				Name: ({$sessioninfo.u})<br />
				NRIC:
			</td>
		
			<td width="30%" valign="bottom" class="small">
				_________________<br />
				Confirmed By<br />
				Name: (Transporter)<br />
				NRIC:
			</td>

			<td width="40%" valign="bottom" class="small">
				_________________<br />
				Checked By (Internal Use)<br />
				Name: (Store Inventory Supervisor)<br />
				NRIC:
			</td>
		</tr>
	</table>
	</div>
{/if}
</div>

<!-- print sheet for vendor -->
<div class=printarea>
<table width=100% cellpadding=4 cellspacing=0 border=0>
<tr>
<td width=25% align=left nowrap><h4>{$branch.description}</td>
<td width=50% align=center nowrap><h4>DEBIT NOTE
{if $copy_type ne 'vendor_copy'}
	{assign var=cols_add value=0}
{else}
	{if $form.sku_type eq 'CONSIGN'}
		{assign var=cols_add value=-1}
	{else}
		{assign var=cols_add value=0}
	{/if}
{/if}
</td>
<td width=25% align=right><h4>DN: {$branch.branch_code}{$grn.id|string_format:"%05d"}<div class=small>(Vendor Copy){if $page}<br />{$page}{/if}</div></h4></td>
</tr>
</table>

<div class=small style="padding-bottom:10px">
{$branch.address|nl2br}
<br>
Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if}
</div>

<table width=100% border=0 cellspacing=0 cellpadding=4 class="tb">
<tr>
	<td><b>Vendor</b></td>
	<td colspan="3">{$grr.vendor_code}{if $grr.account_id} - {$grr.account_id}{/if} - {$grr.vendor|default:'&nbsp;'}</td>
	<td><b>Date Added</b></td>
	<td colspan="3">{$grr.added|date_format:"`$config.dat_format` %I:%M%p"}</td>
</tr>
<tr>
	<td><b>Department</b></td>
	<td colspan="3">{$grn.department|default:'&nbsp;'}</td>
	<td><b>Printed By</b></td>
	<td colspan="3">{$sessioninfo.u|default:'&nbsp;'}</td>
</tr>
<tr>
	<td><b>GRR No.</b></td>
	<td>GRR{$grn.grr_id|string_format:"%05d"}/{$grn.grr_item_id}</td>
	<td><b>GRR Date</b></td>
	<td>{$grr.added|date_format:"%d/%m/%Y"}</td>
	<td nowrap><b>GRR Amount(RM)</b></td>
	<td align="right">{if $grr.grr_amount}{$grr.grr_amount|number_format:2}{/if}</td>
</tr>
<tr>
	<td><b>Lorry No</b></td>
	<td>{$grr.transport|default:'&nbsp;'}</td>
	<td><b>Total Ctn / Pcs</b></td>
	<td align="right">{$grr.ctn|default:'0'} / {$grr.pcs|default:'0'}</td>
	<td><b>Account Adjustment</b></td>
	<td align="right">{$grn.buyer_adjustment|number_format:2|default:'&nbsp;'}</td>
</tr>
<tr>
	<td><b>Document Type.</b></td><td><font color=blue>{$grr.type}</font></td>
	<td><b>Document No.</b></td><td><font color=blue>{$grr.doc_no}</font></td>
	<td><b>Branch</b></td><td>{$grn.branch_code}</td>
</tr>
{if $grr.type eq 'PO'}
<tr>
	{if $config.grn_summary_show_related_invoice && $grr.type eq 'PO'}
	<td><b>Related Invoice</b></td>
	<td>{$grr.related_invoice}</td>
	{else}
	<td colspan="2">&nbsp;</td>
	{/if}
	{if $grr.type eq 'PO'}
	<td><b>Partial Delivery</b></td>
	<td colspan="3">{if $config.use_grn_future}{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}{else}{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}{/if}</td>
	{else}
	<td colspan="2">&nbsp;</td>
	<td colspan="2">&nbsp;</td>
	{/if}
</tr>
{/if}
</table>

<br>
{if $items && !$new}
<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">
<tr  bgcolor=#cccccc>
	<th>&nbsp;</th>
	<th>ARMS Code/<br>MCode</th>
	<th>Article No{if $config.link_code_name}/<br>{$config.link_code_name}{/if}</th>
	<th width="40%">Description</th>
	<th>Cost Price<br />(Original)</th>
	<th>Cost Price<br />(Acc. Adjusted)</th>
</tr>
{assign var=t_page value=0}
{assign var=ttl_qty value=0}
{assign var=ttl_amt value=0}
{foreach from=$items key=r item=item name=i}
	<!--{$t_page++}-->

	<tr  id="tbrow_{$item.id}" bgcolor="{cycle values="#eeeeee,"}" class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_last_page}td_btm_got_line{/if}">
		{if !$page_item_info.$r.not_item}
			<td align=right>
			<!--{$line_no++}-->
			{$start_counter+$smarty.foreach.i.iteration}.
			</td>
			<td>{$item.sku_item_code}<br>{$item.mcode}</td>
			<!--td>{$item.mcode|default:"&nbsp;"}</td-->
			
			<td>{$item.artno|default:"&nbsp;"}{if $config.link_code_name}<br>{$item.link_code}{/if}</td>
			{if $config.link_code_name}
			<!--td>{$item.link_code|default:"&nbsp;"}</td-->
			{/if}
		{else}
			<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
		{/if}
		<td>{$item.description}</td>'
		{if !$page_item_info.$r.not_item}
			<td align=right>{$item.cost|number_format:$config.global_cost_decimal_points}</td>
			<td align=right>{$item.acc_cost|number_format:$config.global_cost_decimal_points}</td>
		{else}
			<td>&nbsp;</td><td>&nbsp;</td>
		{/if}
	</tr>
{/foreach}

{repeat s=$t_page+1 e=$PAGE_SIZE name=rr}
	<tr height=30 class="no_border_bottom">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
{/repeat}

{*if $show_total and $smarty.request.a eq 'print'}
	<tr class="total_row">
		<th colspan={$cols+$cols_add} align=right class="total_row">Total</th>
		<td align=right class="total_row">{$ttl_qty|qty_nf}</td>
		<td align=right class="total_row">{$ttl_amt|number_format:2}</td>
	</tr>
	{if $is_last_page}
		<tr class="total_row">
			<th colspan={$cols+$cols_add} align=right class="total_row">Grand Total</th>
			<td align=right class="total_row">{$total_qty|qty_nf}</td>
			<td align=right class="total_row">{$total_amt|number_format:2}</td>
		</tr>
	{/if}
{else}
    <tr class="total_row">
		<td colspan="{$cols+2}">&nbsp;</td>
	</tr>
{/if*}
</table>
{/if}

<br />
<b>Note</b>
<div style="border:1px solid #000;padding:5px;height:30px;">
	1. We will pay based on the price in our PO and the quantity in our GRN (Goods Received Notes).
	<br />
	2. We will not perform any statement of account reconciliation
</div>

{if $is_last_page}
	<br />
	<b>Remark</b>
	<div style="border:1px solid #000;padding:5px;height:20px;">
	{$grn.acc_action|default:"-"}
	</div>

	<br />
	<div style="border:2px solid #000; padding:1px;">
	<br />
	<br />
	<table width="70%">
		<tr>
			<td width="30%" valign="bottom" class="small">
				_________________<br />
				Issued By<br />
				Name: ({$sessioninfo.u})<br />
				NRIC:
			</td>
		
			<td width="30%" valign="bottom" class="small">
				_________________<br />
				Confirmed By<br />
				Name: (Transporter)<br />
				NRIC:
			</td>

			<td width="40%" valign="bottom" class="small">
				_________________<br />
				Checked By (Internal Use)<br />
				Name: (Store Inventory Supervisor)<br />
				NRIC:
			</td>
		</tr>
	</table>
	</div>
{/if}
</div>