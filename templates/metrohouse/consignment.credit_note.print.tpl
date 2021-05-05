{*
6/8/2010 10:11:36 AM Andy
- CN/DN Swap

8/11/2010 5:52:57 PM Andy
- Change the word of CN from "Lost Item" to "Over Item".
- Change the word of DN from "Over Item" to "Lost Item"

9/3/2010 10:57:49 AM Andy
- Add print branch code for deliver to branch.

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/9/2011 6:42:09 PM Andy
- Change printing font size bigger.

5/24/2011 4:11:01 PM Andy
- Fix missing SKU description in CN/DN printing.

6/8/2011 4:20:54 PM Andy
- Fix "article / mcode" header.
- Change deliver to address format.

7/15/2011 2:19:55 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

1/21/2015 10:46 AM Justin
- Enhanced to have GST calculation.

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing
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
var doc_no = '{$form.inv_no}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}


{if $form.type eq 'lost' or $form.type eq 'over'}
  {assign var=show_per value=1}
{/if}

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
            	{if $sheet_type eq 'cn'}
					<b>CREDIT NOTE<br>(Over Item)</b>
				{else}
				    <b>DEBIT NOTE<br>(Lost Item)</b>
				{/if}
  			</b>
		  </div>
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>Invoice No.</td><td nowrap>{$form.inv_no}</td></tr>
	    <tr height=22><td nowrap>Invoice Date</td><td nowrap>{$form.date|date_format:"%d/%m/%Y"}</td></tr>
	    <tr height=22><td nowrap>Terms.</td><td nowrap>{$to_branch.con_terms|default:"--"}</td></tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td>
	<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
	<tr>		
		<td valign=top width=50% style="border:1px solid #000; padding:5px;padding-left:50px;">
		<h4>Deliver To: {$to_branch.code} - {$to_branch.description}</h4>
		{$to_branch.address|nl2br}<br>
		Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
		{if $to_branch.phone_3}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fax: {$to_branch.phone_3}{/if}
		</td>
	</tr>
	</table>
</td>
</tr>
</table>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">

<tr bgcolor=#cccccc >
	<th width=5>&nbsp;</th>
	<th width=50>ARMS Code</th>
	<th width=50>Article<br>/MCode</th>
	<th>SKU Description</th>
	<th nowrap width=40 align="right">Qty</th>
	<th width=40>U.Price<br>(RM)</th>
	<th width=80>Amount<br>(RM)</th>
	<th width="30">%</th>
	<th width="80">NET</th>
	{if $form.is_under_gst}
		<th width=80>GST</th>
		<th width=80>Net<br />Incl. GST</th>
	{/if}
</tr>

{assign var=counter value=0}

{foreach name=i from=$items item=r key=item_index}
<!-- {$counter++} -->
<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	{if !$page_item_info.$item_index.not_item}
		<td align=center>{$r.item_no+1}.</td>{*<td align=center>{$start_counter+$counter}.</td>*}
		<td align=center>{$r.sku_item_code}</td>
		<td nowrap>{$r.artno_mcode|default:"-"}</td>
	{else}
		<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	{/if}
	
	<td><div class="crop">{$r.sku_description}</div></td>
	
	{if !$page_item_info.$item_index.not_item}
		<td align="right">{$r.pcs}</td>
		<td align="right">{$r.cost_price|number_format:2}</td>

		{assign var=amt_ctn value=$r.cost_price*$r.ctn}
		{assign var=amt_pcs value=$r.cost_price/$r.uom_fraction*$r.pcs}
		{assign var=total_row value=$amt_ctn+$amt_pcs}

		{if $r.discount}
			{assign var=discount_per value=$r.discount*0.01}
			{assign var=discount_amount value=$total_row*$discount_per}
			{assign var=total_row value=$total_row-$discount_amount}
		{/if}

		{assign var=total_row value=$total_row|round2}
		{assign var=total value=$total+$total_row}
		<td align="right">{$total_row|number_format:2}</td>
		<td align="right">{$r.discount_per|ifzero:'&nbsp;':'%'}</td>
		{assign var=total_row value=$total_row-$r.discount_amt}
		<td align="right">{$total_row|number_format:2}</td>
		{if $form.is_under_gst}
			<td align="right">{$r.item_gst|number_format:2}</td>
			<td align="right">{$r.item_gst_amt|number_format:2}</td>
			{assign var=total_gst value=$total_gst+$r.item_gst}
			{assign var=total_gst_amt value=$total_gst_amt+$r.item_gst_amt}
			{assign var=total_gst2 value=$total_gst2+$r.item_gst2}
			{assign var=total_gst_amt2 value=$total_gst_amt2+$r.item_gst_amt2}
		{/if}

		{assign var=total2 value=$total2+$total_row}
		{assign var=total_ctn value=$r.ctn+$total_ctn}
		{assign var=total_pcs value=$r.pcs+$total_pcs}
	{else}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if $form.is_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	{/if}
</tr>
{/foreach}

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
  	<td>&nbsp;</td>
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
	<td align=right nowrap colspan="1">Sub Total</td><td>&nbsp;</td>
	<td align=right>{$total|number_format:2}</td>
	<td>&nbsp;</td>
	<td align="right">{$total2|number_format:2}</td>
	{if $form.is_under_gst}
		<td align=right>{$total_gst|number_format:2}</td>
		<td align=right>{$total_gst_amt|number_format:2}</td>
	{/if}
</tr>
<tr>
	<td align=left nowrap colspan="3">
		{if $form.discount eq "50"}
			BEAR HALF
		{else}
			Discount ({$form.discount|default:'0'}%)
		{/if}
	</td><td>&nbsp;</td>
	{if $form.is_under_gst}
		<td align=right>{$form.gross_discount_amount|number_format:2}</td>
		<td align=right>{$form.sheet_gst_discount|number_format:2}</td>
	{/if}
	<td align=right>{$form.discount_amount|number_format:2}</td>
</tr>
{if $form.discount_amount}{assign var=actual_total value=$total2-$form.discount_amount}{/if}
<tr>
	<td align="right">Total</td>
	<td align=right>{$total_pcs}</td>
	<td>&nbsp;</td><td>&nbsp;</td>
	{if $form.is_under_gst}
		<td align=right>{$form.total_gross_amt|number_format:2}</td>
		<td align=right>{$form.total_gst_amt|number_format:2}</td>
	{/if}
	<td align=right>{$form.total_amount|number_format:2}</td>
</tr>
{assign var=total value=0}
{assign var=total2 value=0}
{assign var=actual_total value=0}
{assign var=total_ctn value=0}
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
