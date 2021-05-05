{*
12/21/2011 10:16:28 AM Andy
- New DO printing templates for reswar.

12/22/2011 10:44:35 AM Andy
- Change to only show column Qty and UOM.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print
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
<table width=100% cellspacing="3" cellpadding=0 border=0 class="">
	<tr>
		<!-- Logo -->
		<td>
			{if !$config.do_print_hide_company_logo}<img src="{get_logo_url mod='do'}" height="80" hspace="5" vspace="5">{else}&nbsp;{/if}
		</td>
		
		<!-- From branch address -->
		<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<h4>From </h4>
			<b>{$from_branch.description}</b><br>
			{$from_branch.address|nl2br}<br>
			Tel: {$from_branch.phone_1|default:"-"}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
			{if $from_branch.phone_3}<br>Fax: {$from_branch.phone_3}{/if}
		</td>
			
		<!-- to address -->
		<td valign=top width="50%" style="border:1px solid #000; padding:5px">
			{if !$form.do_branch_id && $form.open_info.name}
				<h4>To</h4>
				<b>{$form.open_info.name}</b><br>
				{$form.open_info.address}<br>
		    {elseif $form.do_type eq 'credit_sales'}
				<h4>To</h4>
				<b>{$form.debtor_description}</b><br>
				{$form.debtor_address}<br>
			{else}
				<h4>Deliver To</h4>
				<b>{$to_branch.description} </b><br>
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
		<td align=right>
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
			<tr bgcolor="#cccccc" height=22><td nowrap>DO No.</td><td nowrap>{$form.do_no}</td></tr>
			{if !$config.do_printing_allow_hide_date or !$no_show_date}
				<tr height=22><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
			{/if}
			<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
			<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr-->
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
			<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
		  	</table>
		</td>
	</tr>
</table>

<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">

<tr bgcolor=#cccccc>
	<th width=5>&nbsp;</th>
	<th nowrap>ARMS Code</th>
	<th nowrap>Article<br>/MCode</th>
	<th width="90%">SKU Description</th>
	<th nowrap width="80">Qty</th>
	<th>UOM</th>
</tr>

{assign var=counter value=0}

{section name=i loop=$do_items}
<!-- {$counter++} -->
<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align="center" nowrap>{$start_counter+$counter}.</td>
	<td align="center" nowrap>{$do_items[i].sku_item_code|default:'-'}</td>
	<td align="center" nowrap>{if $do_items[i].artno <> ''}{$do_items[i].artno}{else}{$do_items[i].mcode|default:'&nbsp;'}{/if}</td>
	<td><div class="crop">{$do_items[i].description}</div></td>
	<td align="right">{$do_items[i].pcs|qty_nf}</td>
	<td>{$do_items[i].uom_code}</td>
	{assign var=amt_ctn value=$do_items[i].cost_price*$do_items[i].ctn}
	{assign var=amt_pcs value=$do_items[i].cost_price/$do_items[i].uom_fraction*$do_items[i].pcs}
	{assign var=total_row value=$amt_ctn+$amt_pcs}
	{assign var=total_row value=$total_row|round2}
	{assign var=total value=$total+$total_row}
	{assign var=total_ctn value=$do_items[i].ctn+$total_ctn}
	{assign var=total_pcs value=$do_items[i].pcs+$total_pcs}
	
</tr>
{/section}

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
</tr>
{/section}

{if $is_lastpage}
<tr class="total_row">
    <th align=right colspan="4">Total</th>
	<th align=right>{$total_pcs|qty_nf}</th>
	<td>&nbsp;</td>
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

