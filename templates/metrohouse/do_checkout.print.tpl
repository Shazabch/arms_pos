{*
4/2/2010 10:51:59 AM Andy
- Printing remove vertical line

5/13/2010 9:40:30 AM Andy
- add auto break line for cash & credit sales DO address

5/20/2010 10:26:58 AM Andy
- Swap qty and price column.
- Add branch code under deliver to.

5/31/2010 5:01:32 PM Andy
- Move Qty & U.Price left one Column , and add one empty column to seprate with total amount.

9/8/2010 4:27:15 PM Andy
- Printing format changes, reduce the deliver to details line

5/10/2011 10:13:04 AM Andy
- Change printing font size bigger.

5/27/2011 5:40:09 PM Justin
- Amended the Deliver To address to take from DO when found user use Address Deliver To.

7/15/2011 2:33:08 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

10/7/2011 3:42:59 PM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point 

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

10/21/2013 3:13 PM Andy
- Change price to only show 2 decimal.

5/15/2015 1:22 PM Justin
- Enhanced to change title from "Delivery Order" to "Transfer Note".

5/21/2015 10:20 AM Andy
- Enhanced to always print deliver to branch GST Reg No.

12/28/2015 2:10 PM Qiu Ying
- SKU Additional Description should show in document printing

2/19/2016 3:22 PM Qiu Ying
- Hide Sub Total Row and Discount Row
- Add sheet_price_type with description

5/10/2016 3:23 PM Andy
- Change S and P to "SUPER BEST BUY ITEM".
*}

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
body{
  font-size:9pt;
}

table.tb2 {
    border-collapse: collapse;
    border-right:1px solid #000;
    border-bottom:1px solid #000;
}
.tb{
	border-left: none !important;
	font-size: 11pt;
}
.tb th,.tb td{
	border-right: none !important;
}
.artno_col{
	padding: 0 20px;
	text-align:left;
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
<table width=100% cellspacing=0 cellpadding=0 border=0 class="" style="font-size:11pt;">
<tr>
	<td>{*{if !$config.do_print_hide_company_logo}<img src="{get_logo_url mod='do'}" height=80 hspace=5 vspace=5>{else}&nbsp;{/if}*}</td>
	<td width=100%>
	{*<h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2>
	{$from_branch.address|nl2br}<br>
	Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
	{if $from_branch.phone_3}
	&nbsp;&nbsp; Fax: {$from_branch.phone_3}
	{/if}
	*}
	<img src="templates/metrohouse/address.jpg" width="500" height="100" />	
	</td>
	<td rowspan=2 align=right>
	    <table class="large">
		<tr><td colspan=2>
<div style="background:#000;padding:4px;color:#fff" align=center><b>
{if $form.do_type eq 'open'}Cash Bill<br />{elseif $form.do_type eq 'credit_sales'}Credit Sales<br />{/if}TRANSFER NOTE</b></div>
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>DO No.</td><td nowrap>{$form.do_no}</td></tr>
	    <tr height=22><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		<tr height=22><td nowrap>Terms.</td><td nowrap>{$to_branch.con_terms|default:"--"}</td></tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	<table width=100% cellspacing=5 cellpadding=0 border=0 height="100px">
	<tr>
		{if !$form.do_branch_id && $form.open_info.name}
			<td valign=top style="border:1px solid #000; padding:5px">
			<h4>To: {$form.open_info.name}</h4>
			{$form.open_info.address|nl2br}<br>
			</td>		
        {elseif $form.do_type eq 'credit_sales'}
		    <td valign=top style="border:1px solid #000; padding:5px">
			<h4>To: {$form.debtor_description}</h4>
			{$form.debtor_address|nl2br}<br>
			</td>
		{else}
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<h4>Deliver To: {$to_branch.code} - {$to_branch.description}</h4>
			{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
				{$to_branch.address|nl2br}
			{else}
				{$form.address_deliver_to|nl2br}
			{/if}
			<br>
			{if $to_branch.gst_register_no}GST Registration No.: {$to_branch.gst_register_no}<br />{/if}
			Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
			{if $to_branch.phone_3}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fax: {$to_branch.phone_3}{/if}
			
			</td>
		{/if}

	</tr>
	</table>
</td>
</tr>
</table>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">

<tr bgcolor=#cccccc class="top_line">
	<th width=5>&nbsp;</th>
	<th nowrap>ARMS Code</th>
	<th>Article<br>/MCode</th>
	<th width="90%">SKU Description</th>
	<th nowrap width=40 align="right">Qty</th>
	<th width="5%" align="right">U.Price<br>(RM)</th>
	<th width="40">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
	<th width="5%">Total Amount<br>(RM)</th>
</tr>

{assign var=counter value=0}

{section name=i loop=$do_items}
<!-- {$counter++} -->
<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align=center>
		{if !$page_item_info[i].not_item}
			{$do_items[i].item_no+1}.
		{else}
			&nbsp;
		{/if}
	</td>
	<td align="center">{$do_items[i].sku_item_code}</td>
	<td class="artno_col" nowrap>{if $do_items[i].artno <> ''}{$do_items[i].artno}{else}{$do_items[i].mcode|default:'&nbsp;'}{/if}</td>
	<td width="90%"><div {if !$page_item_info[i].no_crop}class="crop"{/if}>{$do_items[i].description}</div></td>
	{if !$page_item_info[i].not_item}
		<td align="right">{$do_items[i].pcs|qty_nf}</td>
		<td align="right">{$do_items[i].cost_price|number_format:2}</td>
		{assign var=row_amt value=$do_items[i].cost_price*$do_items[i].pcs}
		
		<td>&nbsp;</td>
		<td align="right">{$row_amt|number_format:2}</td>
		
		{assign var=total_pcs value=$do_items[i].pcs+$total_pcs}
		{assign var=total_amt value=$total_amt+$row_amt}
	{else}
		<td>&nbsp;</td>
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
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/section}

{if $is_lastpage}
<tr class="total_row">
  <tr class="total_row">
  {assign var=cols value=4}
  <td rowspan="4" colspan="{$cols}">Remark<br />{$form.remark|default:"-"|nl2br}</td>
  <td rowspan="4">&nbsp;</td>
  {assign var=cols value=1}
  <td align="right" colspan="{$cols}" style="border-bottom:none;">Total</td>
  <td align=right style="border-bottom:none;">{*{$total_pcs}*}&nbsp;</td>
  <td align=right style="border-bottom:none;">{$total_amt|number_format:2}</td>
</tr>
<tr>
	<td style="border:none;" colspan=3></td>
</tr>
<tr>
	{if $form.sheet_price_type}
		{if $form.sheet_price_type == "M"}
			{assign var=price_type value = "NORMAL ITEM"}
		{elseif $form.sheet_price_type == "H"}
			{assign var=price_type value = "OFFER 50% ITEM"}
		{elseif $form.sheet_price_type == "C"}
			{assign var=price_type value = "OFFER 70% ITEM"}
		{elseif $form.sheet_price_type == "A" or $form.sheet_price_type == "S" or $form.sheet_price_type == "P"}
			{assign var=price_type value = "SUPER BEST BUY ITEM"}
		{else}
			{assign var=price_type value = "BEST BUY ITEM"}
		{/if}
		<td style="border:1px solid gray;border-right:1px solid gray !important;" colspan=3>{$form.sheet_price_type} = {$price_type}</td>
	{else}
		<td nowrap style="border:none;" colspan=3></td>
	{/if}
</tr>
<tr>
	<td  colspan=3></td>
</tr>
{*
<tr>
  <td align=right nowrap colspan="{$cols}">
    {if $form.markup_type eq 'up'}
      Markup
    {else}
      Discount
    {/if}
    ({$form.do_markup|default:'0'}%)
  </td><td>&nbsp;</td>
   {if $form.do_markup_arr.0}
    {assign var=markup_per value=$form.do_markup_arr.0/100}
    {assign var=markup_amt value=$markup_per*$total_amt}
    {assign var=total_markup_amt value=$total_markup_amt+$markup_amt}
    {assign var=total_amt value=$total_amt+$markup_amt}
  {/if}
  {if $form.do_markup_arr.1}
    {assign var=markup_per value=$form.do_markup_arr.1/100}
    {assign var=markup_amt value=$markup_per*$total_amt}
    {assign var=total_markup_amt value=$total_markup_amt+$markup_amt}
    {assign var=total_amt value=$total_amt+$markup_amt}
  {/if}
  <td align=right>{$total_markup_amt|abs|number_format:2}</td>
</tr>
{if $form.discount_amount}{assign var=total value=$total-$form.discount_amount}{/if}
<tr>
  <td align="right" colspan="{$cols}">Total</td>
	<td align=right>{*{$total_pcs}&nbsp;</td>
  <td align=right>{$total_amt|number_format:2}</td>
</tr>*}
{assign var=total_amt value=0}
{assign var=total_pcs value=0}
{/if}

</table>

{if $is_lastpage}
<b>Additional Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.checkout_remark|default:"-"|nl2br}
</div>
<br>
<table width=100%>
<tr height="140">

<td valign=bottom class="">
_________________<br>
Issued By<br>
Name: {$form.owner_fullname}<br>
Date:
</td>

<td valign=bottom class="">
_________________<br>
Received By<br>
Name:<br>
Date:
</td>
</tr>
</table>
{/if}
</div>
