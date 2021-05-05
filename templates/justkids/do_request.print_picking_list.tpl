{*
2/7/2013 5:17 PM Andy
- New custom picking list printing for justkids.
*}
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
<body onload="window.print();">
{/if}

{assign var=from_branch value=$branches_info.from_branch}
{assign var=to_branch value=$branches_info.to_branch}
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">


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
		
		<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<h4>Deliver To</h4>
			<b>{$to_branch.description}</b><br>
			{$to_branch.address|nl2br}<br>
			Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
			{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
		</td>

	</tr>
	</table>
</td>

		<td rowspan=2 nowrap align="center" valign="top" class="xlarge">
	    <b>DO Request<br />Picking List</b><br />ID#{$pid|string_format:'%05d'}<br /><br>
		{$pdate|date_format:$config.dat_format}<br><br>
		{$page}
		</td>
</tr>
</table>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">

<tr bgcolor=#cccccc>
	<th width="5">&nbsp;</th>
	<th width="50">ARMS Code</th>
	<th width="50">MCode</th>
	<th width="50">Art No.</th>
	<th width="80%">SKU Description</th>
	{if $config.do_request_picking_list_show_remark}<th>Remark</th>{/if}
	<th width="40">UOM</th>
	<th width="40">Request Qty</th>
	<th width="50">Pick Qty</th>
	<th width="50">Verify Qty</th>
	<th nowrap width="40">Latest Stock Balance</th>
</tr>
{assign var=counter value=0}
{foreach from=$items item=r}
	<!-- {$counter++} -->
	<tr class="no_border_bottom">
		<td>{$start_counter+$counter}</td>
		<td>{$r.sku_item_code}</td>
		<td>{$r.mcode|default:'&nbsp;'}</td>
		<td>{$r.artno|default:'&nbsp;'}</td>
		<td><div class="crop">{$r.description|default:'&nbsp;'}</div></td>
		{if $config.do_request_picking_list_show_remark}
		    <td><div class="crop">{$r.comment}</div></td>
		{/if}
		<td align="center">EACH</td>
		<td align="right">{$r.request_qty|qty_nf}</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td align="right">{$r.latest_stock_bal|qty_nf}</td>
	</tr>
	{assign var=total_pcs value=$r.request_qty+$total_pcs}
{/foreach}

{repeat s=$counter+1 e=$PAGE_SIZE}
<!-- filler -->
<tr height=20 class="no_border_bottom">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $config.do_request_picking_list_show_remark}<td>&nbsp;</td>{/if}
</tr>
{/repeat}

{if $is_lastpage}
    {assign var=cols value=8}
    {if $config.do_request_picking_list_show_remark}{assign var=cols value=$cols+1}{/if}
	<th align="right" colspan="{$cols}">Total</th>
	<th align="right">{$total_pcs|number_format}</th>
	<th>&nbsp;</th>
</tr>
{assign var=total_pcs value=0}
{/if}
</table>

{if $is_lastpage}
<br>

<table width=100%>
<tr height=80>
<td valign=bottom class=small>
_________________<br>
Issued By<br>
Name: {$sessioninfo.fullname}<br>
Date:
</td>

<td valign=bottom class=small>
{*_________________<br>
Received By<br>
Name:<br>
Date:*}
</td>
</tr>
</table>
{/if}
<p align=center class=small>** This document is for reference purpose only **</p>  

</div>
