{*
12/21/2009 12:11:48 PM Andy
- Add additional feature to allow user using customized templates and row num in Cash Bill DO

1/26/2010 1:24:56 PM Andy
- Amount under total not BOLD now

3/24/2010 10:01:25 AM Andy
- Change font size larger for branch info, sheet info and total row.

4/8/2010 4:02:24 PM Andy
- Branch Name & description font smaller
- SKU Description font bigger
- Fix all column width

6/18/2010 11:34:11 AM Andy
- remove container left & right line.

6/23/2010 11:04:11 AM Andy
- Make description column take as long as it can.

10/11/2010 5:53:53 PM Andy
- make the item row font bigger.

10/12/2010 1:48:07 PM Justin
- Make the item row font smaller but remain the large font for those digits format such as qty, ctn.
- Reduce the column top and bottom padding for item row and total row. (Andy)

10/18/2010 4:48:11 PM Justin
- make the ARMS and Mcode bigger font as Description.

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

7/15/2011 2:40:59 PM Andy
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

.s1{
    font-size:0.8em;
}
.s2{
    font-size:1.2em;
}

.got_top, .got_top th{
    border-top: 1px solid black ;
}
.got_btm, .got_btm th{
    border-bottom: 1px solid black ;
}
.got_left{
    border-left: 1px solid black;
}
.got_right{
    border-right: 1px solid black ;
}
.tbody_container td, .tbody_container th{
	padding:1px 4px;
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
<table cellspacing=0 cellpadding=0 border=0 class="" width="100%" style="margin-bottom:1px;">
<tr>
    <td>{if !$config.do_print_hide_company_logo}<img src="{get_logo_url mod='do'}" height=80 hspace=5 vspace=5>{else}&nbsp;{/if}</td>
	<td width=100%>
	<h3>{$from_branch.description}</h3>
	<span class="s1">
	{$from_branch.address|nl2br}<br>
	Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
	{if $from_branch.phone_3}
	&nbsp;&nbsp; Fax: {$from_branch.phone_3}
	{/if}
	<span>
	</td>
	<td rowspan=2 align=right>
	    <table >
		<tr><td colspan=2>
<div style="background:#000;color:#fff" align=center><b>
	Cash Bill<br />
	DELIVERY ORDER<br>
		{if $is_draft}
			(DRAFT)
		{elseif $is_proforma}
			(Proforma)
		{else}
			(Pre-Checkout)
		{/if}
		</b></div>
		</td></tr>
		<tr bgcolor="#cccccc" height=20><td nowrap>DO No.</td><td nowrap>{$form.do_no}</td></tr>
	    <tr height=20><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		<tr height=20><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr-->
		<tr bgcolor="#cccccc" height=20><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=20><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td colspan="2">
	<table width=100% height="80px" style="border:1px solid #000;border-collapse:collapse;">
	<tr>
		<td valign=top >
		<h4>To</h4>
		<b>{$form.open_info.name}</b><br>
		{$form.open_info.address}<br>
		</td>
	</tr>
	</table>
</td>
</tr>
</table>

<table border=0 cellspacing=0 cellpadding=4  width="100%" class="small">

<tr bgcolor="#cccccc" class="got_top got_btm">
	<th rowspan=2 width=5 class="got_left got_right">&nbsp;</th>
	<th rowspan=2 width="" nowrap class="got_right">ARMS Code</th>
	<th rowspan=2 width="" nowrap class="got_right">Article<br>/MCode</th>
	<th rowspan=2 class="got_right" width="90%">SKU Description</th>
	{if !$hide_RSP}<th rowspan=2 width="50" class="got_right">RSP<br>(RM)</th>{/if}
	<th rowspan=2 width="40" class="got_right">UOM</th>
	<th nowrap colspan=2 width=80 class="got_right">Qty</th>
</tr>

<tr bgcolor="#cccccc" class="got_btm">
	<th nowrap width=40 class="">Ctn</th>
	<th nowrap width=40 class="got_left got_right">Pcs</th>
</tr>
<tbody class="tbody_container">
{assign var=counter value=0}

{section name=i loop=$do_items}
<!-- {$counter++} -->
<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align="center" nowrap class="got_left">
		{if !$page_item_info[i].not_item}
			{$do_items[i].item_no+1}.
		{else}
			&nbsp;
		{/if}
	</td>
	<td align="center" class="s2" nowrap>{$do_items[i].sku_item_code}</td>
	<td align="center" class="s2" nowrap>{if $do_items[i].artno <> ''}{$do_items[i].artno}{else}{$do_items[i].mcode}{/if}</td>
	<td width="90%">{if !$page_item_info[i].no_crop}<div class="crop">{/if}{$do_items[i].description}{if !$page_item_info[i].no_crop}</div>{/if}</td>
	
	{if !$page_item_info[i].not_item}
		{if !$hide_RSP}<td align="right" class="xlarge">{$do_items[i].selling_price|number_format:2}</td>{/if}

		<td align="center" class="xlarge">{$do_items[i].uom_code|default:"EACH"}</td>

		<td align="right" class="xlarge">{$do_items[i].ctn|qty_nf}</td>
		<td align="right" class="got_right xlarge">{$do_items[i].pcs|qty_nf}</td>
		
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
		<td class="got_right xlarge">&nbsp;</td>
	{/if}
	
</tr>
{/section}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td class="got_left">&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if !$hide_RSP}<td>&nbsp;</td>{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td class="got_right xlarge">&nbsp;</td>
</tr>
{/section}

{if $is_lastpage}
<tr class="total_row got_btm">
  	<th colspan="{if !$hide_RSP}6{else}5{/if}" class="got_left got_right"><div align="right" style="float:right;" class="xlarge">Total</div></th>
	<th align=right class="got_right xlarge">{$total_ctn|qty_nf}</th>
	<th align=right class="got_right xlarge">{$total_pcs|qty_nf}</th>
</tr>
{assign var=total value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}
{/if}
</tbody>
</table>

{if $is_lastpage}
<table  width="100%">
<tr height=80>

<td valign=bottom class=small>
_________________<br>
Issued By<br>
Name: {$form.owner_fullname}<br>
Date:
</td>

<td valign=bottom class=small>
_________________<br>
Approved By<br>
Name:<br>
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
</div>
