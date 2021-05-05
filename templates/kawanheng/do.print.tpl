{*
8/28/2019 9:59 AM William
- Enhanced to added new column for new config "do_custom_column".
- Enhanced do print hide "Ctn" and hide "UOM" and display "Master UOM" when config "do_alt_print_template" is using "kawanheng/do.print.tpl".
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


<!-- print sheet -->
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
{if $system_settings.logo_vertical eq 1}
	<td colspan="2" width="100%">
		<table width="100%" style="text-align: center;">
		{if !$config.do_print_hide_company_logo}
			<tr>
				<td><img style="max-width: 600px;max-height: 80px;" src="{get_logo_url mod='do'}" height="80" hspace="5" vspace="5"></td>
			</tr>
		{else}
			<tr>
				<td>&nbsp;</td>
			</tr>
		{/if}
		{if $system_settings.verticle_logo_no_company_name neq 1}
			<tr>
				<td><h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2></td>
			</tr>
		{/if}
			<tr>
				<td>{$from_branch.address}</td>
			</tr>
			<tr>
				<td>
					Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
					{if $from_branch.phone_3}
					&nbsp;&nbsp; Fax: {$from_branch.phone_3}
					{/if}
					{if $config.enable_gst and $from_branch.gst_register_no}
						 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
					{/if}
				</td>
			</tr>
		</table>
	</td>
	{else}
	<td>
	{if !$config.do_print_hide_company_logo}
		<img src="{get_logo_url mod='do'}" height="80" hspace="5" vspace="5">
	{else}
	&nbsp;
	{/if}
	</td>
	<td width=100%>
	<h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}
		&nbsp;&nbsp; Fax: {$from_branch.phone_3}
		{/if}
		{if $config.enable_gst and $from_branch.gst_register_no}
			 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
		{/if}
	</td>
	{/if}
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:4px;color:#fff" align=center><b>
	{if $form.do_type eq 'open'}Cash Bill<br />{elseif $form.do_type eq 'credit_sales'}Credit Sales<br />{/if}
DELIVERY ORDER<br>
		{if $is_draft}
			(DRAFT)
		{elseif $is_proforma}
			(Proforma)
		{else}
			(Pre-Checkout)
		{/if}
		</b></div>
		<br>
		</td></tr>
		<tr bgcolor="#cccccc" height="22">
			<td nowrap>DO No.</td>
			<td {if $form.approved eq 1 && $config.print_document_barcode}align="center" style="padding:0;"{/if} nowrap>
				{if $form.approved eq 1 && $config.print_document_barcode}
					<span class="barcode3of9" style="padding:0;">
						*{$form.do_no|replace:'/':'/O'}*
					</span>
				{/if}
				
				<div {if $form.approved eq 1 && $config.print_document_barcode}style="margin-top:-5px;"{/if}>
					{$form.do_no}
				</div>
			</td>
		</tr>
		{if !$config.do_printing_allow_hide_date or !$no_show_date}
			<tr height="22"><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
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

<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">

<tr bgcolor=#cccccc>
	<th rowspan=1 width=5>&nbsp;</th>
	<th rowspan=1 nowrap>
		{if $config.replace_docs_arms_code_with_link_code}
			{$config.link_code_name|default:'Old Code'}
		{else}
			ARMS Code
		{/if}
	</th>
	<th rowspan=1 nowrap>Article<br>/MCode</th>
	<th rowspan=1 width="90%">SKU Description</th>
	{if !$hide_RSP}<th rowspan=2 width=40>RSP<br>({$config.arms_currency.symbol})</th>{/if}
	{*
	{if !$config.do_print_hide_cost}
		<th rowspan=1 width=40>Price<br>({$config.arms_currency.symbol})</th>
	{/if}
	*}
	<th rowspan=1 width=40>Master UOM</th>
	{*
	{if !$config.do_print_hide_cost}
		<th rowspan=1 width=40>Ctn Price<br>({$config.arms_currency.symbol})</th>
	{/if}
	*}
	<th rowspan=1 width=80>Pcs</th>
	{*
	{if !$config.do_print_hide_cost}
	<th rowspan=1 width=80>Total Amount<br>({$config.arms_currency.symbol})</th>
	{/if}
	*}
	{if $config.do_custom_column.delivery_carton}
		<th rowspan="1">{$config.do_custom_column.delivery_carton.desc}</th>
	{/if}
</tr>


{assign var=counter value=0}
{foreach from=$do_items key=item_index item=r name=i}
{assign var=custom_pcs value=$r.uom_fraction*$r.ctn+$r.pcs}
<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align="center" nowrap>
		
		{if !$page_item_info.$item_index.not_item}
			{$r.item_no+1}.
		{else}
			&nbsp;
		{/if}
	
	</td>
	<td align="center" nowrap>
		{if $config.replace_docs_arms_code_with_link_code}
			{$r.link_code|default:'&nbsp;'}
		{else}
			{$r.sku_item_code|default:'&nbsp;'}
		{/if}
	</td>
	<td align="center" nowrap>
		{if $r.oi}
			{$r.artno_mcode|default:'&nbsp;'}
		{else}
			{if $r.artno}{$r.artno|default:'&nbsp;'}{else}{$r.mcode|default:'&nbsp;'}{/if}
		{/if}
	</td>
	<td width="90%">{if !$page_item_info.$item_index.no_crop}<div class="crop">{/if}{$r.description|default:'&nbsp;'}{if !$page_item_info.$item_index.no_crop}</div>{/if}</td>
	
	
	{if !$page_item_info.$item_index.not_item}
		{if !$hide_RSP}<td align="right">{if $r.oi == ''}{$r.selling_price|number_format:2}{else}-{/if}</td>{/if}
		<td align=center>{$r.master_uom_code|default:"EACH"}</td>	
		<td align="right">{$custom_pcs|qty_nf}</td>
		{if $config.do_custom_column.delivery_carton}
			<td>{$r.custom_col.delivery_carton}</td>
		{/if}
		{assign var=amt_ctn value=$r.cost_price*$r.ctn}
		{assign var=amt_pcs value=$r.cost_price/$r.uom_fraction*$r.pcs}
		{assign var=total_row value=$amt_ctn+$amt_pcs}

		{assign var=total_row value=$total_row|round2}
		{assign var=total value=$total+$total_row}
		{assign var=total_ctn value=$r.ctn+$total_ctn}
		{assign var=total_pcs value=$r.pcs+$total_pcs}
		{assign var=custom_total_pcs value=$custom_pcs+$custom_total_pcs}
	{else}
		{if !$hide_RSP}<td>&nbsp;</td>{/if}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if $config.do_custom_column.delivery_carton}
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
	{if !$hide_RSP}<td>&nbsp;</td>{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $config.do_custom_column.delivery_carton}
		<td>&nbsp;</td>
	{/if}
</tr>
{/section}

{if $is_lastpage}
<tr class="total_row">
{*
{if !$config.do_print_hide_cost}
	<th align=right colspan=6 >Total</th>
{else}
	<th align=right colspan=4>Total</th>
{/if}
*}
	{assign var=cols value=5}
	{if !$hide_RSP}{assign var=cols value=$cols+1}{/if}
    <th align=right colspan="{$cols}" >Total</th>
	<th align=right>{$custom_total_pcs}</th>
{*
{if !$config.do_print_hide_cost}
	<th align=right>{$total|number_format:2}</th>
{/if}
*}
{if $config.do_custom_column.delivery_carton}
	<th></th>
{/if}
</tr>
{assign var=total value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}
{assign var=custom_total_pcs value=0}
{/if}

</table>

{if $is_lastpage}
<br>
<b>Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.remark|default:"-"|nl2br}
</div>

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
Received By<br>
Name:<br>
Date:
</td>
</tr>
</table>
{/if}
<p align=center class=small>** This document is for reference purpose only **</p>  

</div>

