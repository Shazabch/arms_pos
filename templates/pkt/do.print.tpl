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

11/23/2009 10:08:26 AM Andy
- layout edit

12/14/2009 5:58:48 PM Andy
- column title changed, "Selling" change to "RSP"

1/12/2010 3:51:36 PM Andy
- Add config to manage item got line or not

1/15/2010 9:47:20 AM Andy
- Make description column to occupy as large space as it can

6/18/2010 9:57:47 AM Andy
- Fix empty column bugs.
- remark/additional remark/deliver by put into the box.
- DO format change to same as invoice.
- fix 30 items for last page

6/21/2010 10:24:43 AM Andy
- Move remark/additional remark to every page.
- Add "Continue Next Page" if not the last page.
- Remove line wrap for remark and additional remark.

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/27/2011 5:40:09 PM Justin
- Amended the Deliver To address to take from DO when found user use Address Deliver To.

7/15/2011 2:51:41 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

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
		<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
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
			<b>{$form.debtor_description}</b><br>
			{$form.debtor_address}<br>
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
	<th rowspan=2 nowrap>ARMS Code<br>/MCode</th>
	<th rowspan=2 nowrap>Article</th>
	<th rowspan=2 width="90%">SKU Description</th>
	{if !$hide_RSP}<th rowspan=2 width=40>RSP<br>(RM)</th>{/if}
	<th rowspan=2 width=40>UOM</th>
	<th nowrap colspan=2 width=80>Qty</th>
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>
{assign var=counter value=0}

{section name=i loop=$do_items}
<!-- {$counter++} -->
<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align="center" nowrap>
		{if !$page_item_info[i].not_item}
			{$do_items[i].item_no+1}.
		{else}
			&nbsp;
		{/if}
	</td>
	<td align="center" nowrap>{if $do_items[i].mcode}{$do_items[i].mcode}{else}{$do_items[i].sku_item_code|default:'&nbsp;'}{/if}</td>
	<td align="center" nowrap>{*if $do_items[i].artno <> ''*}{$do_items[i].artno|default:'&nbsp;'}{*else}{$do_items[i].mcode|default:'&nbsp;'}{/if*}</td>
	<td width="90%">{if !$page_item_info[i].no_crop}<div class="crop">{/if}{$do_items[i].description}{if !$page_item_info[i].no_crop}</div>{/if}</td>
	
	{if !$page_item_info[i].not_item}
		{if !$hide_RSP}<td align="right">{$do_items[i].selling_price|number_format:2}</td>{/if}

		<!-- Ctn Price -->
		<td align=center>{$do_items[i].uom_code|default:"EACH"}</td>

		<td align="right">{$do_items[i].ctn|qty_nf}</td>
		<td align="right">{$do_items[i].pcs|qty_nf}</td>
		
		{assign var=amt_ctn value=$do_items[i].cost_price*$do_items[i].ctn}
		{assign var=amt_pcs value=$do_items[i].cost_price/$do_items[i].uom_fraction*$do_items[i].pcs}
		{assign var=total_row value=$amt_ctn+$amt_pcs}

		{assign var=total_row value=$total_row|round2}
		{assign var=total value=$total+$total_row}
		{assign var=total_ctn value=$do_items[i].ctn+$total_ctn}
		{assign var=total_pcs value=$do_items[i].pcs+$total_pcs}
	{else}
		{if !$hide_RSP}<td>&nbsp;</td>{/if}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	
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
	{if !$hide_RSP}<td>&nbsp;</td>{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
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
    <th align=right colspan={if !$hide_RSP}6{else}5{/if} >Total</th>
	<th align=right>{$total_ctn|qty_nf}</th>
	<th align=right>{$total_pcs|qty_nf}</th>
{*
{if !$config.do_print_hide_cost}
	<th align=right>{$total|number_format:2}</th>
{/if}
*}
</tr>
{assign var=total value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}
{/if}

</table>

<div style="height:5px;"></div>
<div style="border:1px solid #000;padding:1px;min-height:15px;">
	<table width="100%">
		<tr>
		    <td nowrap><b>Remark: </b></td>
		    <td width="99%">{$form.remark|default:"-"}</td>
		</tr>
	</table>
</div>
{if $is_lastpage}
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
{else}
	<p align="center"><b>Continue Next Page</b></p>
{/if}
<p align=center class=small>** This document is for reference purpose only **</p>  

</div>
