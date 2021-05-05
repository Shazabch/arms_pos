{*
6/21/2011 2:59:21 PM  Justin
- Fixed the missing line for last row of the page.

7/15/2011 1:22:58 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

3/6/2014 11:44 AM Justin
- Bug fixed on warranty expired is not able to display.
- Enhanced to take off NRIC, Contact No, Email.

3/12/2014 2:42 PM Justin
- Enhanced to take off Name & Address.
- Enhanced to crop description if too long.

5/12/2014 3:04 PM Justin
- Bug fixed on the DO status show wrongly.
*}

<!-- this is the print-out for S/N used by the DO -->
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
		{elseif $form.checkout}
			(Checkout)
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
			{$to_branch.address|nl2br}<br>
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

<table border="0" cellspacing="0" cellpadding="4" width="100%"" class="tb small">

<tr bgcolor="#cccccc">
	<th width="3%">&nbsp;</th>
	<th width="5%" nowrap>ARMS Code</th>
	<th width="{if $form.do_type eq 'transfer'}83%{else}26%{/if}" nowrap>Description</th>
	{if $form.do_type ne "transfer"}
		<th width="3%" nowrap>Warranty<br />Expired</th>
	{/if}
	<th nowrap width="10%">Serial No</th>
</tr>

{assign var=counter value=0}

{if $form.do_type ne "transfer"}
	{foreach from=$do_items item=item key=r}
		{foreach from=$item.serial_no.sn item=sn key=snr}
		<!-- {$counter++} -->
		<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
			<td align="center" nowrap>{$start_counter+$counter}.</td>
			<td align="center" nowrap>{$item.sku_item_code|default:'-'}</td>
			<td class="crop" style="height:2em">{$item.description}</td>
			<td align="center" nowrap>
				{if $item.serial_no.we.$snr && $item.serial_no.we_type.$snr}
					{$item.serial_no.we.$snr|default:'0'} {$item.serial_no.we_type.$snr}(s)
				{else}
					-
				{/if}
			</td>
			<td align="center" nowrap>{$item.serial_no.sn.$snr|default:'&nbsp;'}</td>
		</tr>
		{/foreach}
	{/foreach}
{else}
	{foreach from=$do_items item=item key=r}
		{foreach from=$item.sn item=sn_list key=bid}
			{foreach from=$sn_list item=sn key=r}
				<!-- {$counter++} -->
				<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE}td_btm_got_line{/if}">
					<td align="center" nowrap>{$start_counter+$counter}.</td>
					<td align="center" nowrap>{$item.sku_item_code|default:'-'}</td>
					<td class="crop" style="height:2em">{$item.description}</td>
					<td align="center" nowrap>{$sn}</td>
				</tr>
			{/foreach}
		{/foreach}
	{/foreach}
{/if}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height=20 class="no_border_bottom {if $s2 eq $PAGE_SIZE}td_btm_got_line{/if}">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $form.do_type ne "transfer"}
		<td>&nbsp;</td>
	{/if}
	<td>&nbsp;</td>
</tr>
{/section}

{* if $is_lastpage}
<tr class="total_row">
    <th align=right colspan=6 >Total</th>
	<th align=right>{$total_ctn|number_format}</th>
	<th align=right>{$total_pcs|number_format}</th>
</tr>
{assign var=total value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}
{/if *}

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

