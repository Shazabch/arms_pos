{*
4/2/2010 10:51:59 AM Andy
- Printing remove vertical line

7/23/2010 11:19:02 AM Andy
- Fix Consignment Invoice when multiple print will not show the printing by user name.

9/3/2010 10:54:55 AM Andy
- Add print branch code for deliver to branch.

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/9/2011 6:42:09 PM Andy
- Change printing font size bigger.

6/8/2011 4:20:54 PM Andy
- Fix "article / mcode" header.
- Change deliver to address format.

7/15/2011 2:22:14 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/2/2012 5:38 PM Andy
- Add checking for % and Net column, hide if no item discount.

9/18/2012 3:31 PM Drkoay
- add discount_selling_price_percent and discount_item_row_percent for calculate item price

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

1/21/2015 10:46 AM Justin
- Enhanced to have GST calculation.

3/31/2015 6:24 PM Justin
- Enhanced to show GST registration no.

4/3/2015 5:54 PM Justin
- Enhanced to have GST summary and report changes.

5/11/2015 12:08 PM Justin
- Enhanced to show "TAX INVOICE" instead of "CONSIGNMENT INVOICE".

5/20/2015 2:40 PM Andy
- Change to always show latest artno/mcode.

4/25/2017 8:16 AM Khausalya 
- Enhanced changes from RM to use config setting. 

8/28/2018 11:56 AM Justin
- Enhanced to show/hide Tax Invoice caption.
*}

{if !$skip_header}
{include file='header.print.tpl'}
<style>
{if $config.ci_printing_no_item_line}
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
  margin-left:50px;
}
.tb{
	border-left: none !important;
	font-size: 11pt;
}
.tb th,.tb td{
	border-right: none !important;
}
{/literal}
</style>
<script type="text/javascript">
var doc_no = '{$form.ci_no}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}

{*
{if $form.type eq 'lost' or $form.type eq 'over'}
  {assign var=show_per value=1}
{/if}
*}

<!-- print sheet -->
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="" style="font-size:11pt;">
<tr>
	<td width=100%>
	{*<h2>{$from_branch.description}</h2>
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
      <div style="background:#000;padding:4px;color:#fff" align=center>
      <b>
      {if $form.type eq 'lost'}DEBIT NOTE<br>(Lost Item)
      {elseif $form.type eq 'over'}CREDIT NOTE<br>(Over Item)
      {else}{if $form.is_under_gst}TAX{else}CONSIGNMENT{/if} INVOICE{/if}</b></div>
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>Invoice No.</td><td nowrap>{$form.ci_no}</td></tr>
	    <tr height=22><td nowrap>Invoice Date</td><td nowrap>{$form.ci_date|date_format:"%d/%m/%Y"}</td></tr>
	    <tr height=22><td nowrap>Terms.</td><td nowrap>{$to_branch.con_terms|default:"--"}</td></tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|upper|default:'&nbsp;'}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td>
	<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
	<tr>		
		{if !$form.ci_branch_id && $form.open_info.name}
			<td valign=top style="border:1px solid #000; padding:5px;padding-left:50px;">
			<h4>To</h4>
			<b>{$form.open_info.name}</b><br>
			{$form.open_info.address}<br>
			</td>		
		{else}
			<td valign=top width=50% style="border:1px solid #000; padding:5px;padding-left:50px;">
			<h4>Deliver To: {$to_branch.code} - {$to_branch.description}</h4>
			{$to_branch.address|nl2br}<br>
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

<tr bgcolor=#cccccc class="small">
	<th width=5>&nbsp;</th>
	<th width=50>ARMS Code</th>
	<th width=50>Article<br>/MCode</th>
	<th>SKU Description</th>
	<th width=40>U.Price<br>({$config.arms_currency.symbol})</th>
    <th nowrap width=40 align="right">Qty</th>
	<th width=80>Amount<br>({$config.arms_currency.symbol})</th>
	{if $form.show_per and $form.got_item_discount}
		<th width="30">%</th>
		<th width="80">Net</th>
	{/if}
	{if $form.is_under_gst}
		<th width="80">GST<br />({$config.arms_currency.symbol})</th>
		<th width="80">Net<br />Incl. GST<br />({$config.arms_currency.symbol})</th>
	{/if}
</tr>

{assign var=counter value=0}

{section name=i loop=$ci_items}
<!-- {$counter++} -->
<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align=center>{$start_counter+$counter}.</td>
	<td align=center>{$ci_items[i].sku_item_code}</td>
	<td nowrap>{$ci_items[i].artno|default:$ci_items[i].mcode|default:"-"}</td>
	<td><div class="crop">{if $form.is_under_gst}{$ci_items[i].indicator_receipt} {/if}{$ci_items[i].description}</div></td>
	<td align="right">{$ci_items[i].cost_price/$ci_items[i].uom_fraction|number_format:2}</td>
    <td align="right">{$ci_items[i].pcs}</td>
	

	{assign var=amt_ctn value=$ci_items[i].cost_price*$ci_items[i].ctn}
	{assign var=amt_pcs value=$ci_items[i].cost_price/$ci_items[i].uom_fraction*$ci_items[i].pcs}
	{assign var=total_row value=$amt_ctn+$amt_pcs}
	{assign var=default_total value=$default_total+$total_row}
	
	<td align="right">{$total_row|number_format:2}</td>
	{if $form.show_per and $form.got_item_discount}		
		{assign var=total_row_tmp value=$total_row}
		
		{if $form.discount_selling_price_percent}
			{assign var=disc_arr value="+"|explode:$form.discount_selling_price_percent}
			{foreach from=$disc_arr item=disc}
				{assign var=discount_amt value=$total_row*$disc*0.01}				
				{assign var=total_row value=$total_row-$discount_amt}
			{/foreach}
			{assign var=total_row_tmp value=$total_row}
		{/if}
		
		{if $form.discount_item_row_percent}
			{assign var=disc_arr value="+"|explode:$form.discount_item_row_percent}
			{foreach from=$disc_arr item=disc}
				{assign var=discount_amt value=$total_row*$disc*0.01}
				{assign var=total_row value=$total_row-$discount_amt}
			{/foreach}			
		{/if}		
		
		{foreach from=$ci_items[i].disc_arr item=disc}
			{assign var=discount_per value=$disc*0.01}
			{assign var=discount_amount value=$total_row_tmp*$discount_per}
			{assign var=total_row value=$total_row-$discount_amount}
			{assign var=total_row_tmp value=$total_row_tmp-$discount_amount}
		{/foreach}
		<td align="right">{$ci_items[i].discount|default:'0'}%</td>
		<td align="right">{$total_row|number_format:2}</td>
	{/if}
	{if $form.is_under_gst}
		<td align="right">{$ci_items[i].item_gst|number_format:2}</td>
		<td align="right">{$ci_items[i].item_gst_amt|number_format:2}</td>
	{/if}

	{assign var=total_row value=$total_row|round2}
	{assign var=total value=$total+$total_row}
	{assign var=total_ctn value=$ci_items[i].ctn+$total_ctn}
	{assign var=total_pcs value=$ci_items[i].pcs+$total_pcs}
	{if $form.is_under_gst}
		{assign var=total_gst value=$total_gst+$ci_items[i].item_gst}
		{assign var=total_gst_amt value=$total_gst_amt+$ci_items[i].item_gst_amt}
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
	{if $form.show_per and $form.got_item_discount}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/section}



{if $is_lastpage}
<tr class="total_row">
	{assign var=cols value=4}
	<td rowspan="3" colspan="{$cols}">Remark<br />{$form.remark|default:"-"|nl2br}</td>
	{assign var=cols value=1}
	<td align=right nowrap colspan="{$cols}">Sub Total</td><td>&nbsp;</td>
	<td align=right>{$default_total|number_format:2}</td>
	{if $form.show_per and $form.got_item_discount}
		<td>&nbsp;</td>
		<td align="right">{$total|number_format:2}</td>        
	{/if}
	{if $form.is_under_gst}
		<td align="right">{$total_gst|number_format:2}</td>
		<td align="right">{$total_gst_amt|number_format:2}</td>
	{/if}
</tr>
<tr>
	<td align=right nowrap colspan="{$cols}">
	{if $form.type eq 'lost' or $form.type eq 'over'}
		BEAR HALF
	{else}
		Disc ({$form.discount_percent|default:'0'}%)
	{/if}
	</td><td>&nbsp;</td>
	{if $form.show_per and $form.got_item_discount}
		<td>&nbsp;</td><td>&nbsp;</td>
	{/if}
 	{if $form.is_under_gst}
		<td align=right>{$form.gross_discount_amount|number_format:2}</td> 
		<td align=right>{$form.sheet_gst_discount|number_format:2}</td> 
	{/if}
	<td align=right>{$form.discount_amount|number_format:2}</td>
</tr>
<tr>
  <td align="right" colspan="{$cols}">Total</td>
	<td align=right>{$total_pcs}</td>
	{if $form.show_per and $form.got_item_discount}
    <td>&nbsp;</td><td>&nbsp;</td>
  {/if}
 	{if $form.is_under_gst}
		<td align=right>{$form.total_gross_amt|number_format:2}</td> 
		<td align=right>{$form.total_gst_amt|number_format:2}</td> 
	{/if}
  <td align=right>{$form.total_amount|number_format:2}</td>  
</tr>
{assign var=total value=0}
{assign var=default_total value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}
{/if}

</table>

{if $is_lastpage}
<br>

<table width=100%>
<tr height=80>

{if $form.is_under_gst}
	<td valign=top style="padding-right:10px;" width="30%">
		{include file='consignment.gst_summary.tpl'}
	</td>
{/if}

<td valign=bottom class=small>
_________________<br>
Issued By<br>
Name: {$sessioninfo.fullname}<br>
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
