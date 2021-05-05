{*
10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
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
<table width=100% cellspacing=0 cellpadding=0 border=0>
<tr valign=top>
	<td><img src="{get_logo_url mod='adjustment'}" height=80 hspace=5 vspace=5></td>
	<td width=100% class="small">
	<h2>{$form.b_description} {if $form.b_company_no}({$form.b_company_no}){/if}</h2>
	{$form.b_address|nl2br}<br>
	Tel: {$form.b_phone_1}{if $form.b_phone_2} / {$form.b_phone_2}{/if}
	{if $form.b_phone_3}
	&nbsp;&nbsp; Fax: {$form.b_phone_3}
	{/if}
	</td>
	<td rowspan="2" align=right>
	    <table>
			<tr height=22><td colspan=2 style="background:#000;padding:4px;color:#fff" align=center><b>Adjustment</b></td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Adj No.</td><td nowrap>{$form.report_prefix}{$form.id|string_format:"%05d"}</td></tr>
			{if $form.offline_id}
				<tr height=22 bgcolor="#cccccc"><td nowrap>Offline ID</td><td nowrap>#{$form.offline_id|string_format:"%05d"}</td></tr>
			{/if}
		    <tr height=22><td nowrap>Adj Date</td><td nowrap>{$form.adjustment_date|date_format:$config.dat_format}</td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
			<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td colspan="2">
		<table class="normal" border=0 cellspacing=0 cellpadding=1>
			<tr>
			<th align=left nowrap>Department : </th>
			<td align=left width=10%>{$form.dept}</td>
			<th align=left nowrap>Adjustment Type : </th>
			<td align=left width=30%>{$form.adjustment_type}</td>
			{if $form.remark}
			<th align=left nowrap>Remark : </th>
			<td align=left width=30%>{$form.remark}</td>
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
	<th nowrap width="10%">Serial No</th>
</tr>

{assign var=counter value=0}

{foreach from=$items item=item key=r}
	{foreach from=$item.sn item=sn_list key=bid}
		{foreach from=$sn_list item=sn key=r name=serial_no}
			<!-- {$counter++} -->
			<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE}td_btm_got_line{/if}">
				<td align="center" nowrap>{$start_counter+$counter}.</td>
				<td align="center" nowrap>{$sn_count} {$item.sku_item_code|default:'-'}</td>
				<td nowrap>{$item.description}</td>
				<td align="center" nowrap>{$sn}</td>
			</tr>
		{/foreach}
	{/foreach}
{/foreach}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height=20 class="no_border_bottom {if $s2 eq $PAGE_SIZE}td_btm_got_line{/if}">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/section}

</table>

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
{/if}
<p align=center class=small>** This document is for reference purpose only **</p>  

</div>

