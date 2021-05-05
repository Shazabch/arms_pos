{*
7/1/2008 2:32:38 PM yinsee
- change 15 to $PAGE_SIZE

8/12/2008 12:29:18 PM yinsee
- auto crop at 1em for description column

6/22/2009 5:00 PM Andy
- Add No horizontal line setting

10/7/2009 10:16 AM Andy
- Add print draft and proforma

11/10/2009 2:50:43 PM Andy
- title of "Cash Sales" changed to "Cash Bill"

11/12/2009 5:40:50 PM Andy
- layout edited, artile no and ctn price hide

11/25/2009 10:16:21 AM Andy
- Fix total row colspan

12/14/2009 1:36:40 PM Andy
- Fix total row colspan
- column title changed, "Selling" change to "RSP"

2/25/2010 2:09:21 PM Andy
- Add link code column, title follow config

5/26/2010 11:36:41 AM Andy
- Add print sales order data.(customer PO & batch code)

5/27/2010 10:40:53 AM Andy
- Add debtor code and area on printing

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/27/2011 5:40:09 PM Justin
- Amended the Deliver To address to take from DO when found user use Address Deliver To.

7/15/2011 3:03:13 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/10/2011 3:12:22 PM Andy
- Add nowrap for artno for all DO printing templates.

10/7/2011 3:42:59 PM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point 

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

9/25/2015 5:34 AM DingRen
- hide RSP colummn on do print by follow config

12/28/2015 1:10 PM Qiu Ying
- SKU Additional Description should show in document printing
*}
<!-- this is the print-out for approved but non-checkout DO -->
{if !$skip_header}
{include file='header.print.tpl'}
<style>
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
	<td width=100%>
	<h2>{$from_branch.description}</h2>
	{$from_branch.address|nl2br}<br>
	Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
	{if $from_branch.phone_3}
	&nbsp;&nbsp; Fax: {$from_branch.phone_3}
	{/if}
	</td>
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
		<tr bgcolor="#cccccc" height=22><td nowrap>DO No.</td><td nowrap>{$form.do_no}</td></tr>
	    <tr height=22><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		<tr height=22><td nowrap>PO No.</td>
			<td nowrap>
			    {if $sales_order_data}
			        {$sales_order_data.cust_po|default:'--'}
			    {else}
					{$form.po_no|default:"--"}
				{/if}
			</td>
		</tr>
		{if $sales_order_data}
		    <tr height="22">
		        <td nowrap>Contract No</td>
		        <td nowrap>{$sales_order_data.batch_code}</td>
		    </tr>
		{/if}
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr-->
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td>
	<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
	<tr>
		<td valign=top width=50% style="border:1px solid #000; padding:5px">
		<h4>From </h4>
		<b>{$from_branch.description}</b><br>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1|default:"-"}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}<br>Fax: {$from_branch.phone_3}{/if}
		</td>
		
		{if !$form.do_branch_id && $form.open_info.name}
			<td valign=top style="border:1px solid #000; padding:5px">
			<h4>To</h4>
			<b>{$form.open_info.name}</b><br>
			{$form.open_info.address}<br>
			</td>
        {elseif $form.do_type eq 'credit_sales'}
		    <td valign=top style="border:1px solid #000; padding:5px">
			<h4>To</h4>
			<b>{$form.debtor_code} - {$form.debtor_description}</b><br>
			{$form.debtor_address}<br>
			{$form.debtor_area}
			</td>
		{else}
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<h4>Deliver To</h4>
			<b>{$to_branch.description}</b><br>
			{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
				{$to_branch.address|nl2br}
			{else}
				{$form.address_deliver_to|nl2br}
			{/if}
			<br>
			Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
			{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
			</td>
		{/if}

	</tr>
	</table>
</td>
</tr>
</table>

<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">

<tr bgcolor=#cccccc>
	<th rowspan=2 width=5>&nbsp;</th>
	<th rowspan=2 width=50>ARMS Code/<br>MCode</th>
	<th rowspan=2 width=50>Artno</th>
	<th rowspan="2" width="50">{$config.link_code_name|default:'Old Code'}</th>
	<th rowspan=2>SKU Description</th>
	{if !$hide_RSP}<th rowspan=2 width=40>RSP<br>(RM)</th>{/if}
	{if !$config.do_print_hide_cost}
		<th rowspan=2 width=40>Price<br>(RM)</th>
	{/if}
	<th rowspan=2 width=40>UOM</th>
	{if !$config.do_print_hide_cost}
		{*<th rowspan=2 width=40>Ctn Price<br>(RM)</th>*}
	{/if}
	<th nowrap colspan=2 width=80>Qty</th>
	{if !$config.do_print_hide_cost}
	<th rowspan=2 width=80>Total Amount<br>(RM)</th>
	{/if}
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>
{assign var=counter value=0}

{section name=i loop=$do_items}
<!-- {$counter++} -->
<tr class="no_border_bottom">
	<td align=center>
		{if !$page_item_info[i].not_item}
			{$do_items[i].item_no+1}.
		{else}
			&nbsp;
		{/if}
	</td>
	<td align=center>{if $do_items[i].mcode <> ''}{$do_items[i].mcode}{else}{$do_items[i].sku_item_code|default:'&nbsp;'}{/if}</td>
	<td nowrap>{$do_items[i].artno|default:'&nbsp;'}</td>
	<td>{$do_items[i].link_code|default:"&nbsp;"}</td>
	<td width="90%">{if !$page_item_info[i].no_crop}<div class="crop">{/if}{$do_items[i].description}{if !$page_item_info[i].no_crop}</div>{/if}</td>
	
	{if !$page_item_info[i].not_item}
		{if !$hide_RSP}<td align="right">{$do_items[i].selling_price|number_format:2}</td>{/if}
		{if !$config.do_print_hide_cost}
			<td align="right">{$do_items[i].cost_price/$do_items[i].uom_fraction|number_format:$config.global_cost_decimal_points}</td>
		{/if}
		<td align=center>{$do_items[i].uom_code|default:"EACH"}</td>
		{if !$config.do_print_hide_cost}
			{*<td align="right">
			{if $do_items[i].uom_fraction>1}
				{$do_items[i].cost_price|number_format:$config.global_cost_decimal_points}
			{else}
				-
			{/if}
			</td>*}
		{/if}
		<td align="right">{$do_items[i].ctn|qty_nf}</td>
		<td align="right">{$do_items[i].pcs|qty_nf}</td>
		
		{assign var=amt_ctn value=$do_items[i].cost_price*$do_items[i].ctn}
		{assign var=amt_pcs value=$do_items[i].cost_price/$do_items[i].uom_fraction*$do_items[i].pcs}
		{assign var=total_row value=$amt_ctn+$amt_pcs}
		{if !$config.do_print_hide_cost}
		<td align="right">{$total_row|number_format:2}</td>
		{/if}
		{assign var=total_row value=$total_row|round2}
		{assign var=total value=$total+$total_row}
		{assign var=total_ctn value=$do_items[i].ctn+$total_ctn}
		{assign var=total_pcs value=$do_items[i].pcs+$total_pcs}
	{else}
		{if !$config.do_print_hide_cost}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
		{if !$hide_RSP}<td>&nbsp;</td>{/if}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/section}

{repeat s=$counter+1 e=$PAGE_SIZE}
<!-- filler -->
<tr height=20 class="no_border_bottom">
{if !$config.do_print_hide_cost}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
{/if}
  	{if !$hide_RSP}<td>&nbsp;</td>{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/repeat}

{if $is_lastpage}
<tr class="total_row">
{assign var=cols value=7}
{if !$config.do_print_hide_cost}
{assign var=cols value=$cols+1}
{/if}
{if $hide_RSP}
{assign var=cols value=$cols-1}
{/if}
	<th align=right colspan="{$cols}" >Total</th>
	<th align=right>{$total_ctn|qty_nf}</th>
	<th align=right>{$total_pcs|qty_nf}</th>
{if !$config.do_print_hide_cost}
	<th align=right>{$total|number_format:2}</th>
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
