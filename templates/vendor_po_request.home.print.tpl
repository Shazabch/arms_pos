{*
08.05.2008 17:59:03 Saw
- change "Vendor Draft Purchase Order" to "Vendor Propose Item List"
- delete "Category" column
- add "(pcs)" for balance

11/4/2010 12:52:35 PM Justin
- Added FOC column.
- Added config to check whether need to display the order price or not.
- Added to print extra empty column.
- Fixed some of the bugs for this report such as unable to display solid line at last column.

7/15/2011 3:14:46 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

2/13/2017 2:01 PM Andy
- Change loop from section to foreach.

3/8/2018 5:02 PM Justin
- Bug fixed on empty row printing will still print out even already reaches max 15 items.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
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

<body onload="window.print()">
{/if}

<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 class="small">
<tr>
	<td><img src="{get_logo_url mod='po'}" width=80 hspace=5 vspace=5></td>
	<td width=70%>
	<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
	{$branch.address|nl2br}<br>
	Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if}
	{if $branch.phone_3}
	&nbsp;&nbsp; Fax: {$branch.phone_3}
	{/if}
	</td>
	<td rowspan=2 align=right width=30% valign=top>
	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:2px;color:#fff" align=center><b>Vendor Propose Item List</b></div>
<div class="xsmall">This Vendor Draft PO is waiting for approval, no delivery will be accepted until this PO is fully approved by the authority.</div>
		<br>
		</td></tr>
	    <tr bgcolor="#cccccc"><td nowrap>Department</td><td nowrap>{$form.dept_name}</td></tr>
		<tr bgcolor="#cccccc"><td nowrap>Requested By</td><td nowrap>{$form.fullname_create}</td></tr>
		<tr bgcolor="#cccccc"><td nowrap>PO Owner</td><td nowrap>{$form.fullname}</td></tr>
		<tr bgcolor="#cccccc"><td nowrap>Created Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr>
		<tr bgcolor="#cccccc"><td nowrap >IP Logged</td><td nowrap>{$form.access_ip}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	<table width=100% cellspacing=5 cellpadding=0 border=0>
	<tr>
		<td valign=top style="border:1px solid #000; padding:5px;">
		<h4>Vendor</h4>
		<b>{$form.vendor}</b><br>
		{$form.address|nl2br}<br>
		Tel: {$form.phone_1|default:"-"}{if $form.phone_2} / {$form.phone_2}{/if}
		{if $from.phone_3}<br>Fax: {$from.phone_3}{/if}
		</td>

		<td valign=top style="border:1px solid #000; padding:5px;">
		<h4>Deliver To</h4>
		<b>{$branch.description}</b><br>
		{$branch.address|nl2br}<br>
		Tel: {$branch.phone_1|default:"-"}{if $branch.phone_2} / {$branch.phone_2}{/if}
		{if $branch.phone_3}<br>Fax: {$branch.phone_3}{/if}
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
	<th rowspan=2 nowrap>ArtNo</th>
	<th rowspan=2 nowrap>MCode</th>
	<th width=100% rowspan=2 nowrap>SKU Description</th>
	{if $config.po_vendor_use_order_price}
		<th rowspan=2>Order Price</th>
	{/if}
	<th rowspan=2>Balance<br />(Pcs)</th>
	<th rowspan=2>UOM</th>
	<th colspan=2>Qty</th>
	<th rowspan=2 style="border-bottom:1px solid #000">FOC<br />(Pcs)</th>
	<th rowspan=2 style="border-bottom:1px solid #000">Remark</th>	
</tr>
<tr class="hd topline xsmall">
	<th>Ctn</th>
	<th>Pcs</th>
</tr>
<tr>
<td colspan=10 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>
</tr>

{assign var=counter value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}
{assign var=total_foc value=0}

{foreach from=$items key=sid item=r name=fitem}
	{if $temp.uom_fraction.$sid>1}
		{assign var=total_ctn value=$total_ctn+$temp.qty.$sid}
	{else}
		{assign var=total_pcs value=$total_pcs+$temp.qty.$sid}
	{/if}
	{assign var=total_foc value=$total_foc+$temp.foc.$sid}
	<!-- {$counter++} -->
	<tr height=30 class="rw{cycle name=row values=",2"}">
		<td align=center>{$counter}</td>
		<td nowrap>{$r.sku_item_code}</td>	
		<td nowrap>{$r.artno}</td>
		<td nowrap>{$r.mcode}</td>
		<td>{$r.description}	</td>
		{if $config.po_vendor_use_order_price}
			<td nowrap align=center>{$temp.order_price.$sid|default:"-"}</td>
		{/if}
		<td nowrap align=center>{$temp.balance.$sid|default:"--"}</td>
		<td nowrap align=center>{$temp.uom_code.$sid}</td>
		<td nowrap align=right>{if $temp.uom_fraction.$sid>1}{$temp.qty.$sid}{else}&nbsp;{/if}</td>
		<td nowrap align=right>{if $temp.uom_fraction.$sid eq '1'}{$temp.qty.$sid}{else}&nbsp;{/if}</td>	
		<td nowrap align=center {if $smarty.foreach.fitem.first}style="border-top:1px solid #000"{/if}>{$temp.foc.$sid|default:"-"}</td>
		<td {if $smarty.foreach.fitem.first}style="border-top:1px solid #000"{/if}>{$temp.remark.$sid}</td>
	</tr>
{/foreach}

{section start=$counter+1 loop=15 name=ii}
<!-- filler -->
<tr height=30>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $config.po_vendor_use_order_price}<td>&nbsp;</td>{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td style="background:#ccc">&nbsp;</td>
	<td style="background:#ccc">&nbsp;</td>
	<td {if !$smarty.section.ii.last}style="background:#ccc"{else}style="background:#ccc; border-bottom:1px solid #000"{/if}>&nbsp;</td>
	<td {if $smarty.section.ii.last}style="border-bottom:1px solid #000"{/if}>&nbsp;</td>
</tr>
{/section}

<td colspan=10 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>

<!-- total -->
<tr height=30>
    <th colspan="{if $config.po_vendor_use_order_price}8{else}7{/if}" align=right>Total</th>
	<th style="border-bottom:1px solid #000;" bgcolor="#cccccc" nowrap align=right>{$total_ctn}</th>
	<th style="border-bottom:1px solid #000;" bgcolor="#cccccc" nowrap align=right>{$total_pcs}</th>
	<th style="border-bottom:1px solid #000; border-top:1px solid #000;" bgcolor="#cccccc" nowrap align=right>{$total_foc}</th>
	<th style="border-top:1px solid #000; border-bottom:1px solid #000;" bgcolor="#cccccc" nowrap align=right>&nbsp;</th>
	
</tr>

</table>
</div>

<br><br><br>

<table width=100%>
<tr>
<td width=40% valign=bottom class=small>
______________________________<br>
Created By<br>
Name:
</td>
<td width=30% valign=top class=xsmall>
<b>IMPORTANT:</b><br>
i) Please quote our P/O No. in all your Delivery Order / Invoices.<br>
ii) Kindly supply goods in strict accordance to our P/O.<br>
iii) Valid only if goods according to Trade Description.<br>
iv) Prices quality of merchandise if differ from original sample will be liable for deduction or return.
</td>
<td width=30% valign=top class=xsmall>
<b>NOTICE:</b><br>
NOTICE is hereby given that the undersigned Company will strictly abandond and reject any Supplier who has factoring arrangement with financial institution.
</td>
</tr>
</table>
</div>
