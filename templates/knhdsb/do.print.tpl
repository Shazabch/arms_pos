{*
8/20/2018 3:46 PM Justin
- Modified the printing base on customer request.

9/3/2018 5:56 PM Justin
- Bug fixed system will print extra column when DO is no longer at GST status.
- Amended the signature area to similar with customer request.

10/10/2018 2:31 PM Andy
- Change Transfer DO "Price" column to show "Cost Price".
*}
<!-- this is the print-out for approved but non-checkout DO -->
{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
{if $config.do_printing_no_item_line}
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

{/literal}
</style>


<script type="text/javascript">

var doc_no = '{$form.do_no}';
if (doc_no == '') doc_no = '{$form.prefix}{$form.id|string_format:"%05d"}(DD)';

{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}

{if $form.do_type eq 'transfer' and $config.do_transfer_have_discount}
	{assign var=show_invoice value=1}
{elseif $form.do_type eq 'open' and $config.do_cash_sales_have_discount}
    {assign var=show_invoice value=1}
{elseif $form.do_type eq 'credit_sales' and $config.do_credit_sales_have_discount}
    {assign var=show_invoice value=1}
{/if}

<!-- print sheet -->
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td>
	{if !$config.do_print_hide_company_logo}
		<img src="{get_logo_url mod='do'}" height="80" hspace="5" vspace="5">
	{else}
	&nbsp;
	{/if}
	</td>
	<td width=100%>
	<h2>{$from_branch.description}</h2>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}
		&nbsp;&nbsp; Fax: {$from_branch.phone_3}
		{/if}
		{if $config.enable_gst and $from_branch.gst_register_no}
			 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
		{/if}
	</td>
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:4px;color:#fff" align=center><b>
		{if $form.do_type eq 'open'}
			Cash Bill<br />
			DELIVERY ORDER
		{elseif $form.do_type eq 'credit_sales'}
			Credit Sales<br />
			DELIVERY ORDER
		{else}
			TRANSFER
		{/if}
		<br>
		{if $form.do_type ne 'transfer'}
			{if $is_draft}
				(DRAFT)
			{elseif $is_proforma}
				(Proforma)
			{else}
				(Pre-Checkout)
			{/if}
		{/if}
		</b></div>
		<br>
		</td></tr>
		<tr bgcolor="#cccccc" height="22"><td nowrap>{if $form.do_type eq 'transfer'}Transfer{else}DO{/if} No.</td><td nowrap>{$form.do_no}</td></tr>
		{if !$config.do_printing_allow_hide_date or !$no_show_date}
			<tr height="22"><td nowrap>{if $form.do_type eq 'transfer'}Transfer{else}DO{/if} Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		{/if}
		<tr height="22"><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		{if $form.offline_id}
			<tr height="22" bgcolor="#cccccc"><td nowrap>Offline ID</td><td nowrap>#{$form.offline_id|string_format:"%05d"}</td></tr>
		{/if}
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr-->
		<tr bgcolor="#cccccc" height="22"><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height="22"><td colspan="2" align="center">{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td colspan="2">
	<table width="100%" cellspacing="5" cellpadding="0" border="0" height="120px">
	<tr>
		<td valign="top" width="50%" style="border:1px solid #000; padding:5px">
			<h4>Bill to Address</h4>
			{if $form.do_type ne 'transfer'}
				{if $form.do_type eq 'credit_sales'}
					<b>{$form.debtor_description}</b><br>
					{$form.debtor_address|nl2br}<br>
					Tel: {$form.debtor_phone|default:'-'}<br>
					Terms: {$form.debtor_term|default:'-'}<br>
				{else}
					<b>{$form.open_info.name}</b><br>
					{$form.open_info.address|nl2br}<br />
				{/if}
			{else}
				<b>{$to_branch.description}</b><br>
				{$to_branch.address|nl2br}<br>
				Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
				{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
			{/if}
		</td>
		
		<td valign="top" width="50%" style="border:1px solid #000; padding:5px">
			<h4>Deliver to Address</h4>
			{if $form.do_type ne 'transfer'}
				{if $form.do_type eq 'credit_sales'}
					<b>{$form.deliver_debtor_description|default:$form.debtor_description}</b><br>
					{$form.debtor_deliver_address|nl2br}<br>
					Tel: {$form.deliver_debtor_phone|default:$form.debtor_phone|default:'-'}<br>
					Terms: {$form.deliver_debtor_term|default:$form.debtor_term|default:'-'}<br>
				{else}
					{if $form.use_address_deliver_to}
						<b>{$form.open_info.delivery_name|default:$form.open_info.name}</b><br>
						{$form.open_info.delivery_address|default:$form.open_info.address|nl2br}<br />
					{else}
						<b>{$form.open_info.name}</b><br>
						{$form.open_info.address|nl2br}<br />
					{/if}
				{/if}
			{else}
				<b>{$to_branch.description}</b><br>
				{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
					{$to_branch.address|nl2br}
				{else}
					{$form.address_deliver_to|nl2br}
				{/if}
				<br>
				Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
				{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
			{/if}
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>

{if $form.do_type eq 'transfer'}
	<div align="center"><h3>STOCK TRANSFER NOTE</h3></div>
{/if}
<br />

<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb small">

<tr bgcolor="#cccccc">
	<th rowspan="2" width="5">&nbsp;</th>
	<th rowspan="2" nowrap>ARMS Code</th>
	<th rowspan="2" nowrap>Article<br>/MCode</th>
	<th rowspan="2" width="90%">SKU Description</th>
	{if $form.do_type eq 'transfer'}
		<th rowspan="2" width="40">Price<br>({$config.arms_currency.symbol})</th>
	{elseif !$hide_RSP}
		<th rowspan="2" width="40">RSP<br>({$config.arms_currency.symbol})</th>
	{/if}
	<th rowspan="2" width="40">UOM</th>
	{if $form.do_type eq 'transfer' && $show_invoice}
		<th rowspan="2" width="40">Inv. Discount</th>
	{/if}
	<th nowrap colspan="2" width="80">Qty</th>
	
	{if $form.do_type eq 'transfer'}
		{* GST *}
		{if $form.is_under_gst}
			<th rowspan="2">Gross Amt</th>
			<th rowspan="2">GST Code</th>
			<th rowspan="2" width="40">GST Amt</th>
		{/if}
		
		<th rowspan="2" width="80">Total Amount {if $form.is_under_gst}Included GST{/if}<br>({$config.arms_currency.symbol})</th>
	{/if}
</tr>

<tr bgcolor="#cccccc">
	<th nowrap width="40">Ctn</th>
	<th nowrap width="40">Pcs</th>
</tr>
{assign var=counter value=0}

{foreach from=$do_items key=item_index item=r name=i}

<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align="center" nowrap>
		
		{if !$page_item_info.$item_index.not_item}
			{$r.item_no+1}.
		{else}
			&nbsp;
		{/if}
	
	</td>
	<td align="center" nowrap>{$r.sku_item_code|default:'&nbsp;'}</td>
	<td align="center" nowrap>
		{if $r.oi}
			{$r.artno_mcode|default:'&nbsp;'}
		{else}
			{if $r.artno}{$r.artno|default:'&nbsp;'}{else}{$r.mcode|default:'&nbsp;'}{/if}
		{/if}
	</td>
	<td width="90%">{if !$page_item_info.$item_index.no_crop}<div class="crop">{/if}{$r.description|default:'&nbsp;'}{if !$page_item_info.$item_index.no_crop}</div>{/if}</td>
	
	
	{if !$page_item_info.$item_index.not_item}
		{if $form.do_type eq 'transfer'}
			{assign var=cost_price value=$r.cost_price}
		
			{* DO Markup *}
			{if $form.do_markup_arr.0}
				{assign var=adjust_cost value=$form.do_markup_arr.0*$cost_price/100}
				{assign var=cost_price value=$cost_price+$adjust_cost}
			{/if}
			{if $form.do_markup_arr.1}
				{assign var=adjust_cost value=$form.do_markup_arr.1*$cost_price/100}
				{assign var=cost_price value=$cost_price+$adjust_cost}
			{/if}
			
			{if $r.price_indicator eq 'Last DO' || $r.price_indicator eq 'Cost'} 
				{assign var=cost_decimal_points value=$config.global_cost_decimal_points}
			{else}
				{assign var=cost_decimal_points value=2}
			{/if}
			
			<td align="right">{$cost_price|number_format:$cost_decimal_points}</td>
		{else if!$hide_RSP}
			<td align="right">{if $r.oi == ''}{$r.selling_price|number_format:2}{else}-{/if}</td>
		{/if}
		<td align=center>{$r.uom_code|default:"EACH"}</td>	
		{if $form.do_type eq 'transfer' && $show_invoice}
			<td align="right">{$r.item_discount|default:'-'}</td>
		{/if}
		<td align="right">{$r.ctn|qty_nf}</td>
		<td align="right">{$r.pcs|qty_nf}</td>
		
		{if $form.do_type eq 'transfer'}
			{if $show_invoice}
				{if $form.is_under_gst}
					<td align="right">{$r.inv_line_gross_amt|number_format:2}</td>
					<td align="center">{$r.gst_code}({$r.gst_rate}%)</td>
					<td align="right">{$r.inv_line_gst_amt|number_format:2}</td>
				{/if}

				<td align="right">{$r.inv_line_amt|number_format:2}</td>
			{else}
				{if $form.is_under_gst}
					<td align="right">{$r.line_gross_amt|number_format:2}</td>
					<td align="center">{$r.gst_code}({$r.gst_rate}%)</td>
					<td align="right">{$r.line_gst_amt|number_format:2}</td>
				{/if}
				
				<td align="right">{$r.line_amt|number_format:2}</td>
			{/if}
		{/if}
		
		{assign var=total_ctn value=$r.ctn+$total_ctn}
		{assign var=total_pcs value=$r.pcs+$total_pcs}
	{else}
		{if $form.do_type eq 'transfer' || !$hide_RSP}<td>&nbsp;</td>{/if}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if $form.do_type eq 'transfer'}
			{if $show_invoice}<td>&nbsp;</td>{/if}
			{if $form.is_under_gst}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
			<td>&nbsp;</td>
		{/if}
	{/if}
</tr>
{/foreach}

{section name=s start=0 loop=$extra_empty_row}
<tr height=20 class="no_border_bottom {if $smarty.section.s.iteration eq $extra_empty_row and !$is_lastpage}td_btm_got_line{/if}">
  <td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $form.do_type eq 'transfer' || !$hide_RSP}<td>&nbsp;</td>{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $form.do_type eq 'transfer'}
		{if $show_invoice}<td>&nbsp;</td>{/if}
		{if $form.is_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
		<td>&nbsp;</td>
	{/if}
</tr>
{/section}

{if $is_lastpage}
	<tr class="total_row">
		{assign var=colspan value=5}
		{if $form.do_type eq 'transfer' || !$hide_RSP}{assign var=colspan value=$colspan+1}{/if}
		{if $form.do_type eq 'transfer' && $show_invoice}{assign var=colspan value=$colspan+1}{/if}
		<th align=right colspan="{$colspan}" >Total</th>
		<th align=right>{$total_ctn|qty_nf}</th>
		<th align=right>{$total_pcs|qty_nf}</th>
		{if $form.do_type eq 'transfer'}
			{if $form.is_under_gst}
				{if $show_invoice}
					<th align="right">{$form.inv_sub_total_gross_amt|number_format:2}</th>
					<th>&nbsp;</th>
					<th align="right">{$form.inv_sub_total_gst_amt|number_format:2}</th>
				{else}
					<th align="right">{$form.sub_total_gross_amt|number_format:2}</th>
					<th>&nbsp;</th>
					<th align="right">{$form.sub_total_gst_amt|number_format:2}</th>
				{/if}
			{/if}
			{if $show_invoice}
				<th align="right">{$form.sub_total_inv_amt|number_format:2}</th>
			{else}
				<th align="right">{$form.sub_total_amt|number_format:2}</th>
			{/if}
		{/if}
	</tr>
	{assign var=total value=0}
	{assign var=total_ctn value=0}
	{assign var=total_pcs value=0}
{/if}

</table>

{if $is_lastpage}
<br>
<b>Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.remark|default:"-"|nl2br}
</div>

<b>Additional Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.checkout_remark|default:"-"|nl2br}
</div>
<br>

<table width=100%>
<tr height=80>

<td valign=bottom class=small>
_________________<br>
Issued By<br>
Name: {$form.owner_fullname}<br>
Date:
</td>

<td valign=bottom class=small>
_________________<br>
Approved By<br>
Name:<br>
Date:
</td>

<td valign=bottom class=small>
_________________<br>
Received By<br>
Name:<br>
Date:
</td>
</tr>
</table>
{/if}

</div>

