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

6/17/2010 3:10:10 PM Justin
- ARMS Code column line is missing due to it is Open Item that not having ARMS Code, replace this field by using "-".
- While it is an Open Item, it shows the 0 under Selling Price where Selling Price should not have it, replace this field by using "-".

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/25/2011 1:20:51 PM Andy
- Add company logo.

5/27/2011 5:40:09 PM Justin
- Amended the Deliver To address to take from DO when found user use Address Deliver To.

7/15/2011 1:22:33 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

11/29/2011 04:37:00 PM Andy
- Add to hide DO Date when found user got tick "Don't Show DO Date".

5/17/2013 11:43 AM Andy
- Enhance default printing format to compatible with additional description.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

12/11/2013 2:13 PM Justin
- Enhanced to show offline ID.

6/3/2014 12:00 PM Fithri
- able to set report logo by branch (use config)

4/14/2015 2:43 PM Andy
- Enhanced to show GST ID.

9/25/2015 5:34 AM DingRen
- hide RSP colummn on do print by follow config

12/28/2015 11:50 AM Qiu Ying
- SKU Additional Description should show in document printing
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
		{if $form.offline_id}
			<tr height=22 bgcolor="#cccccc"><td nowrap>Offline ID</td><td nowrap>#{$form.offline_id|string_format:"%05d"}</td></tr>
		{/if}
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr-->
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td colspan="2">
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
			<b>{$to_branch.description} </b><br>
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

	<th rowspan=2 nowrap>ART No</th>
	<th rowspan=2 width="90%">SKU Description</th>
	{if !$hide_RSP}<th rowspan=2 width=40>RSP<br>(RM)</th>{/if}
	{*
	{if !$config.do_print_hide_cost}
		<th rowspan=2 width=40>Price<br>(RM)</th>
	{/if}
	*}
	<th rowspan=2 width=40>UOM</th>
	{*
	{if !$config.do_print_hide_cost}
		<th rowspan=2 width=40>Ctn Price<br>(RM)</th>
	{/if}
	*}
	<th nowrap colspan=2 width=80>Qty</th>
	{*
	{if !$config.do_print_hide_cost}
	<th rowspan=2 width=80>Total Amount<br>(RM)</th>
	{/if}
	*}
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
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
	
	<td align="center" nowrap>{$r.artno|default:'&nbsp;'}</td>
	<td width="90%">{if !$page_item_info.$item_index.no_crop}<div class="crop">{/if}{$r.description|default:'&nbsp;'}{if !$page_item_info.$item_index.no_crop}</div>{/if}</td>
	
	
	{if !$page_item_info.$item_index.not_item}
		{if !$hide_RSP}<td align="right">{if $r.oi == ''}{$r.selling_price|number_format:2}{else}-{/if}</td>{/if}
		<td align=center>{$r.uom_code|default:"EACH"}</td>	
		<td align="right">{$r.ctn|qty_nf}</td>
		<td align="right">{$r.pcs|qty_nf}</td>
		
		{assign var=amt_ctn value=$r.cost_price*$r.ctn}
		{assign var=amt_pcs value=$r.cost_price/$r.uom_fraction*$r.pcs}
		{assign var=total_row value=$amt_ctn+$amt_pcs}

		{assign var=total_row value=$total_row|round2}
		{assign var=total value=$total+$total_row}
		{assign var=total_ctn value=$r.ctn+$total_ctn}
		{assign var=total_pcs value=$r.pcs+$total_pcs}
	{else}
		<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	{/if}
</tr>
{/foreach}

{section name=s start=0 loop=$extra_empty_row}
<tr height=20 class="no_border_bottom {if $smarty.section.s.iteration eq $extra_empty_row and !$is_lastpage}td_btm_got_line{/if}">

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
    <th align=right colspan={if !$hide_RSP}5{else}4{/if} >Total</th>
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

