{*
7/13/2012 3:29:23 PM Justin
- Fixed bug of effective branches cannot be shown.

7/17/2012 5:01:34 PM Justin
- Enhanced to have price type column.

05/26/2016 13:30 Edwin
- Add "Price Change Branch" and "Price Change Date" on Future Change Price Print.
- Enhanced on hide item cost when $config.masterfile_future_batch_price_print_hide_cost is enabled.

05/30/2016 10:50 Edwin
- Change table font size on Future Change Price Print.

7/20/2017 08:33 AM Qiu Ying
- Enhanced to use the artno and mcode in sku item table instead of artno_mcode in do_items and po_items

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

11/9/2018 3:22 PM Justin
- Enhanced to have remark info.
*}
{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
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
<body onload="window.print()">


<!-- print sheet -->
<div class=printarea>
<table class=small align=right cellpadding=4 cellspacing=0 border=0>
	<tr bgcolor=#cccccc>
		<td align=center>
			<b>FUTURE CHANGE PRICE<br />
				{if $form.active && !$form.status}
					(Draft)
				{elseif $form.status && !$form.approved}
					(In Approval Cycle)
				{else}
					(Approved)
				{/if}
			</b>
		</td>
	</tr>
	<tr bgcolor=#cccccc>
		<td align=center><b>{$page}</b></td>
	</tr>
</table>

<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>

<table class="tbd" cellpadding=4 cellspacing=0 border=0 width=100%>
	<tr>
		<td><b>Doc No</b></td>
		<td colspan=3>#{$form.id|string_format:"%05d"}</td>
	</tr>
	<tr>
		<td><b>Created Date</b></td>
		<td {if $form.date_by_branch}colspan="3"{/if}>{$form.added|date_format:$config.dat_format}</td>
		{if !$form.date_by_branch}
			<td><b>Price Change Date</b></td>
			<td>{$form.date|date_format:$config.dat_format} {$form.hour|string_format:"%02d"}:{$form.minute|string_format:"%02d"}</td>
		{/if}
	</tr>
	<tr>
		<td><b>Created Branch</b></td>
		<td>
			{assign var=bid value=$form.branch_id}
			{$branches.$bid.code}
		</td>
		<td><b>Price Change Branch</b></td>
		<td>
			{if $form.date_by_branch}
				{foreach from=$form.effective_branches key=bid item=r name=bl}
					{$branches.$bid.code} ({$r.date|date_format:$config.dat_format} {$r.hour|string_format:"%02d"}:{$r.minute|string_format:"%02d"})
					{if !$smarty.foreach.bl.last}, {/if}
				{foreachelse}
					- None -
				{/foreach}
			{else}
				{foreach from=$form.effective_branches item=r name=bl}
					{$branches.$r.code}
					{if !$smarty.foreach.bl.last}, {/if}
				{/foreach}
			{/if}
		</td>
	</tr>
	<tr>
		<td><b>Created By</b></td>
		<td>{$form.username}</td>
		<td><b>Printed By</b></td>
		<td>{$sessioninfo.u}</td>
	</tr>
</table>

<br>

<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb">

<tr bgcolor="#cccccc">
	<th width=5>&nbsp;</th>
	<th nowrap>ARMS Code</th>
	<th nowrap>Article<br />/MCode</th>
	<th width="90%">SKU Description</th>
	<th width=40>Type</th>
	{if !$config.masterfile_future_batch_price_print_hide_cost}
		<th width=40>Cost</th>
	{/if}
	<th width=40>Price</th>
	<th>Discount<br />Code</th>
	<th width=40>Min Qty</th>
	<th width=40>Proposed Price</th>
</tr>
{assign var=counter value=0}

{foreach from=$items key=r item=item name=i}
	<!-- {$counter++} -->
	<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
		<td align="center" nowrap>{$start_counter+$counter}.</td>
		<td align="center" nowrap>{$item.sku_item_code|default:'-'}</td>
		<td align="center" nowrap>{if $item.artno}{$item.artno|default:'&nbsp;'}{else}{$item.mcode|default:'&nbsp;'}{/if}</td>
		<td width="90%"><div class="crop">{$item.description}</div></td>
		<td align="right">{$item.type}</td>
		{if !$config.masterfile_future_batch_price_print_hide_cost}
			<td align="right">{$item.cost|number_format:$config.global_cost_decimal_points}</td>
		{/if}
		<td align="right">{$item.selling_price|number_format:2}</td>
		<td align="right">{$item.trade_discount_code|default:'-'}</td>
		<td align="right">
			{if $item.type eq 'qprice'}
				{$item.min_qty|default:'0'}
			{else}
				-
			{/if}
		</td>
		<td align="right">{$item.future_selling_price|number_format:2}</td>
	</tr>
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
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if !$config.masterfile_future_batch_price_print_hide_cost}
			<td>&nbsp;</td>
		{/if}
	</tr>
{/section}
</table>

{if $is_lastpage}
<br>
<b>Remark</b>
<div style="border:1px solid #000;padding:5px;height:30px;">
{if $form.remark}
{$form.remark}
{else}
-
{/if}
</div>
{/if}

</div>

