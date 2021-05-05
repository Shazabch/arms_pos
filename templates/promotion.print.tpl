{*
11/4/2010 6:35:31 PM Justin
- Added the checking different type of report printing.
- Added ministry of trade printing format.
- Added checking for show cost.

4/5/2011 4:35:48 PM Andy
- Add checking for $config['promotion_hide_member_options'], if found will hide member column.

7/13/2011 1:57:38 PM Alex
- add net sales column

7/15/2011 2:57:15 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

7/20/2011 10:10:21 AM Justin
- Added Selling Price for Member/Non Member while printing Ministry of Trade format.

9/27/2011 11:58:41 AM Alex
- fix calculation promotion discount

11/18/2011 5:00:10 PM Andy
- Add can use config to control print item per page for discount promotion.

8/30/2012 4:08 PM Justin
- Enhanced to add 2 new columns "Type" and "Limit" for member.

2/5/2013 4:07 PM Fithri
- Promotion printing output show wrong 'Type' P/D

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

2/19/2019 5:55 PM Andy
- Enhanced Print Promotion to use shared template.
- Add can Print by branch.

2/27/2019 2:02 PM Andy
- Added "Printed on".
*}

{assign var=show_member_col value=1}
{if $config.promotion_hide_member_options}
    {assign var=show_member_col value=0}
{/if}

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
var doc_no = '{$form.id}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}

<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td><img src="{get_logo_url mod='promotion'}" height=80 hspace=5 vspace=5></td>
	<td width=100%>{$branch_info.description} {if $branch_info.company_no}({$branch_info.company_no}){/if}<br>
		{$branch_info.address|nl2br}<br>
		Tel: {$branch_info.phone_1}{if $branch_info.phone_2} / {$branch_info.phone_2}{/if}
		{if $branch_info.phone_3}&nbsp;&nbsp; Fax: {$branch_info.phone_3}{/if}
	</td>
	<td rowspan=2 align=right valign=top>

	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:2px;color:#fff" align=center><b>{if $form.status==0}Draft{elseif !$form.approved}Proforma{/if} Promotion</b></div>
{if $form.status==0}
<div class="xsmall">This draft Promotion is for internal use only.</div>
{elseif !$form.approved}
<div class="xsmall">This Promotion is not valid, official Promotion will be issued after approval.</div>
<!--div class="xsmall">This Proforma PO is waiting for approval, no delivery will be accepted until this PO is fully approved by the authority.</div-->
{/if}
		<br>
		</td></tr>
	    <tr bgcolor="#cccccc"><td nowrap>Promotion No.</td><td nowrap>{$form.id}</td></tr>
		<tr><td nowrap>Issued By</td><td nowrap>{$form.fullname}</td></tr>
		<tr bgcolor="#cccccc"><td nowrap>Created on</td><td nowrap>{$form.added|date_format:"%d/%m/%Y"}</td></tr>
		<t ><td nowrap>Printed on</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr>
		<tr bgcolor="#cccccc"><td nowrap>Page</td><td nowrap>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
<table>
<tr>
<td><b>Title:</b> </td>
<td>{$form.title}</td>
</tr>
<tr>
<td valign=top rowspan=2><b>Promotion Period:</b> </td>
<td>{$form.date_from} - {$form.date_to}</td>
</tr>
<tr><td>{$form.time_from} - {$form.time_to}</td></tr>
<tr>
<td><b>Branch(s):</b> </td>
<td>
	{if $print_promo_bid}
		{$branches.$print_promo_bid.code}
	{else}
		{foreach name=i from=$form.promo_branch_id item=b}
			{$b}{if !$smarty.foreach.i.last},{/if}
		{/foreach}
	{/if}
</td>
</tr>
</table>
</td>
</tr>
</table>

<!--- item table -->
<div style="border:2px solid #000; padding:1px;">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="box small">
<tr class="hd topline">
	<th rowspan=2>No</th>
	<th rowspan=2 nowrap>ARMS Code</th>
	<th rowspan=2 nowrap>Mcode</th>
	<th rowspan=2 nowrap>Art No</th>
	<th width=100% rowspan=2 nowrap>SKU Description</th>
	{if !$smarty.request.mot_fmt && $sessioninfo.show_cost}
		<th rowspan=2>Cost</th>
	{/if}
	<th rowspan=2>Selling Price</th>
	{if $show_stock}
		<th rowspan=2>Stock Balance</th>
	{/if}
	
	{if $show_member_col}
		<th colspan={if $form.consignment_bearing ne 'yes'}"5"{else}"6"{/if}>Member</th>
	{/if}
	<th colspan={if $form.consignment_bearing ne 'yes'}"5"{else}"6"{/if}>Non Member</th>
</tr>
<tr class="hd topline">
{if $form.consignment_bearing ne 'yes'}
	<!--- Member -->
	{if $show_member_col}
		<th>Discount</th>
		<th>Price</th>
		<th>Net</th>
		<th>Type</th>
		<th>Limit</th>
	{/if}
{*	<th>Min Items</th>
	<th>Qty From</th>
	<th>Qty To</th>
*}
	<!--- Non Member -->
	<th>Discount</th>
	<th>Price</th>
	<th>Net</th>
{*	<th>Min Items</th>
	<th>Qty From</th>
	<th>Qty To</th>
*}
{else}
	<!--- Member -->
	{if $show_member_col}
		<th>Code</th>
		<th>Profit</th>
		<th>Discount</th>
		<th>Bearing</th>
		<th>Nett Sales</th>
		<th>Net</th>
	{/if}
	<!--- Non Member -->
	<th>Code</th>
	<th>Profit</th>
	<th>Discount</th>
	<th>Bearing</th>
	<th>Nett Sales</th>
	<th>Net</th>
{/if}
</tr>
<tr>
<td colspan="20" style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>
{assign var=counter value=0}
{foreach name=t from=$promo_items item=item key=item_id}
{cycle name=rowbg values="#ddd,#ccc" assign=rowbg}
{assign var=member_net_sp value=0}
{assign var=non_member_net_sp value=0}
<!-- {$counter++} -->
<tr height=15 class="rw{cycle name=crow values=",2"}">
	<td>{$counter}</td>
	<td nowrap>{$item.sku_item_code|default:"-"}</td>
	<td nowrap>{$item.mcode|default:"-"}</td>
	<td nowrap>{$item.artno|default:"-"}</td>
	<td>{$item.description}</td>
	{if !$smarty.request.mot_fmt && $sessioninfo.show_cost}
		<td nowrap align=right>{$item.grn_cost|number_format:3}</td>
	{/if}
	<td nowrap align=right>{$item.selling_price|number_format:3}</td>
	{if $show_stock}
		<td nowrap align="right">{$item.qty|ifzero:"-"}</td>
	{/if}
{if $form.consignment_bearing ne 'yes'}
	<!--- Member -->
	{if $show_member_col}
		<td nowrap align="right">{$item.member_disc_p|ifzero:"-"}</td>
		<td nowrap align="right">{$item.member_disc_a|ifzero:"-"}</td>
	{*	<td nowrap>{$item.member_min_item|ifzero:"-"}</td>
		<td nowrap align="right">{$item.member_qty_from|ifzero:"-"}</td>
		<td nowrap align="right">{$item.member_qty_to|ifzero:"-"}</td>
	*}
		<td nowrap align="right">
			{if $item.member_disc_p}
				{if strpos($item.member_disc_p,'%')}
					{assign var=member_net_per value=$item.selling_price*$item.member_disc_p/100}
					{assign var=member_net_sp value=$item.selling_price-$member_net_per}
				{else}
					{assign var=member_net_sp value=$item.selling_price-$item.member_disc_p}
				{/if}
			{/if}
			{$member_net_sp|number_format:2|ifzero:"-"}
		</td>
		<td nowrap align="center">
			{if $item.control_type eq '1'}
				D
			{elseif $item.control_type eq '2'}
				P
			{/if}
		</td>
		<td nowrap align="right">{$item.member_limit|ifzero:0}</td>
	{/if}
	<!--- Non Member -->
	<td nowrap align="right">{$item.non_member_disc_p|ifzero:"-"}</td>
	<td nowrap align="right">{$item.non_member_disc_a|ifzero:"-"}</td>
{*	<td nowrap>{$item.non_member_min_item|ifzero:"-"}</td>
	<td nowrap align="right">{$item.non_member_qty_from|ifzero:"-"}</td>
	<td nowrap align="right">{$item.non_member_qty_to|ifzero:"-"}</td>
*}
	<td nowrap align="right">
		{if $item.non_member_disc_p}
			{if strpos($item.non_member_disc_p,'%')}
				{assign var=non_member_net_per value=$item.selling_price*$item.non_member_disc_p/100}
				{assign var=non_member_net_sp value=$item.selling_price-$non_member_net_per}
			{else}
				{assign var=non_member_net_sp value=$item.selling_price-$item.non_member_disc_p}
			{/if}

		{/if}
		{$non_member_net_sp|number_format:2|ifzero:"-"}
	</td>
{else}
	<!--- Member -->
	{if $show_member_col}
		<td nowrap align="center">{$item.member_trade_code|default:"-"}</td>
		<td nowrap align="right">{$item.member_prof_p|ifzero:"-":"%"}</td>
		<td nowrap align="right">{$item.member_disc_p|ifzero:"-"}</td>
		<td nowrap align="right">{if $item.member_use_net eq 'no'}{$item.member_net_bear_p|ifzero:"-":"%"}{else}-{/if}</td>
		<td nowrap align="right">{if $item.member_use_net eq 'yes'}{$item.member_net_bear_p|ifzero:"-":"%"}{else}-{/if}</td>
		<td nowrap align="right">
			{if $item.member_disc_p}
				{if strpos($item.member_disc_p,'%')}
					{assign var=member_net_per value=$item.selling_price*$item.member_disc_p/100}
					{assign var=member_net_sp value=$item.selling_price-$member_net_per}
				{else}
					{assign var=member_net_sp value=$item.selling_price-$item.member_disc_p}
				{/if}
			{/if}
			{$member_net_sp|number_format:2|ifzero:"-"}
		</td>
	{/if}
	
	<!--- Non Member -->
	<td nowrap align="center">{$item.non_member_trade_code|default:"-"}</td>
	<td nowrap align="right">{$item.non_member_prof_p|ifzero:"-":"%"}</td>
	<td nowrap align="right">{$item.non_member_disc_p|ifzero:"-"}</td>
	<td nowrap align="right">{if $item.non_member_use_net eq 'no'}{$item.non_member_net_bear_p|ifzero:"-":"%"}{else}-{/if}</td>
	<td nowrap align="right">{if $item.non_member_use_net eq 'yes'}{$item.non_member_net_bear_p|ifzero:"-":"%"}{else}-{/if}</td>
	<td nowrap align="right">

		{if $item.non_member_disc_p}
			{if strpos($item.non_member_disc_p,'%')}
				{assign var=non_member_net_per value=$item.selling_price*$item.non_member_disc_p/100}
				{assign var=non_member_net_sp value=$item.selling_price-$non_member_net_per}
			{else}
				{assign var=non_member_net_sp value=$item.selling_price-$item.non_member_disc_p}
			{/if}
		{/if}
		{$non_member_net_sp|number_format:2|ifzero:"-"}
	</td>
{/if}
</tr>
{/foreach}

{repeat s=$counter+1 e=$page_size}
<!-- filler -->
<tr height="15">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if !$smarty.request.mot_fmt && $sessioninfo.show_cost}
		<td>&nbsp;</td>
	{/if}
	<td>&nbsp;</td>
	{if $show_stock}
		<td>&nbsp;</td>
	{/if}
{if $form.consignment_bearing ne 'yes'}
	<!--- Member -->
	{if $show_member_col}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	<!--- Non Member -->
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
{else}
	<!--- Member -->
	{if $show_member_col}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	<!--- Non Member -->
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>

{/if}
</tr>
{/repeat}

</table>
</div>

<br><br><br>
<table width=100%>
<tr>
{if $config.po_internal_copy_3signatures}
<td valign=bottom class=small>
_________________<br>
Order By<br>
Name:
</td>

<td valign=bottom class=small>
_________________<br>
Approved By<br>
Name:
</td>

<td valign=bottom class=small>
_________________<br>
Accepted By<br>
Name:
</td>

{else}
<td width=50% valign=bottom class=small>
______________________________<br>
Accepted By<br>
Name:
</td>
{/if}

<td valign=bottom align=right nowrap>
<h1>Internal Copy</h1>
</td>
</tr>
</table>

</div>
