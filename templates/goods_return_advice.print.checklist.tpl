{*
Revision History
================
4/19/2007 yinsee
- add return_type + cost column and total row
- rearrange vendor, date etc order

9/21/2007 10:48:14 AM yinsee
- only show total in last page

2/19/2008 3:54:28 PM gary
- separate out the NONSKU and SKU items with pagination.

2/20/2008 11:15:02 AM gary
- add grand total if have nonSKU items.

4/7/2008 4:55:48 PM gary
- add branch tel.

6/22/2009 5:00 PM Andy
- Add No horizontal line setting

12/10/2009 10:49:25 AM Andy
- change date to use form.added (GRA Create Time)

1/12/2010 3:51:36 PM Andy
- Add config to manage item got line or not

7/6/2010 4:33:39 PM Justin
- Modified the report printing to include Selling Price column.

11/2/2010 2:50:42 PM Alex
- fix column bugs if no link_code_name config

4/29/2011 4:21:11 PM Justin
- Rounding is now base on config['gra_cost_decimal_points'], if not found means all round by 2 decimal points.

5/23/2011 12:10:59 PM Justin
- Modified the grand total amount to round by default 2 decimal points instead of follow config set.

7/15/2011 1:47:00 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/1/2011 5:03:32 PM Justin
- Removed the number format for qty.
- Fixed some of the bugs found.
- Enhanced the report sum up for cost amount.

9/24/2012 6:03 PM Justin
- Enhanced to show remark.

4:58 PM 9/26/2012 Justin
- Fixed bug of the "-" show in remark is being line break.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

4/18/2015 5:14 PM Justin
- Enhanced to have GST information.

5/8/2015 11:20 AM Justin
- Enhanced to show document date.

5/20/2015 11:33 AM Justin
- Bug fixed on row amount for item not in ARMS calculate wrongly.

1/22/2016 11:20 AM Qiu Ying
- Show gst_selling_price and selling price
- SKU Additional description should shown in document printing

2/22/2017 3:36 PM Justin
- Enhanced to direct load gst amount from gra item instead of recalculate.

10:23 AM 4/3/2017 Justin
- Enhanced to hide cost including gst information when config "gra_checklist_hide_cost" is turned on.

9/4/2018 5:15 PM Justin
- Bug fixed on showing empty selling price when GRA is not under GST status.

9/14/2018 2:56 PM Justin
- Bug fixed on the wrong column span for total row.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

5/16/2019 5:07 PM William
- Enhance "GRA" word to use report_prefix.

6/27/2019 9:45 AM Andy
- Fixed total row colspan wrong when GRA is consignment.
*}

{if !$skip_header}
{include file='header.print.tpl'}

<style>
{if $config.gra_printing_no_item_line}
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
</style>

<script type="text/javascript">
var doc_no = '{$branch.report_prefix}{$form.id|string_format:"%05d"}';
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

{*if $sku_type!='CONSIGN'*}
<!--<table align=right cellpadding=4 cellspacing=0 border=0>
<tr bgcolor=#cccccc>
	<td align=center><b>GRA CHECKLIST<br>GRA{$form.id|string_format:"%05d"}/{$form.batchno}</b></td>
</tr>
</table>
<h2>{$branch.description}</h2>-->
{*else*}
<table width=100% cellpadding=4 cellspacing=0 border=0>
<tr>
<td width=30% align=left nowrap><h3>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h3>
{$branch.address|nl2br}<br>
Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if}
</td>
<td width=20% align=right><h2>GRA CHECKLIST ({$form.sku_type})<br />{$branch.report_prefix}{$form.id|string_format:"%05d"}/{$form.batchno}</h2><br>{$page}</td>
</tr>
</table>
{*/if*}

<table width=100% border=0 cellspacing=0 cellpadding=4 class="tb">
<tr>
	<td><b>Vendor</b></td>
	<td>{$form.vendor}</td>
	<td><b>Date</b></td>
	<td>{$form.added|date_format:"`$config.dat_format` %I:%M%p"}</td>
</tr>
<tr >
	<td><b>Department</b></td>
	<td>{$form.dept_code}</td>
	<td><b>Printed By</b></td>
	<td>{$sessioninfo.u}</td>
</tr>
</table>

<br>
{if $items && !$new}
<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb xsmall">
<tr  bgcolor=#cccccc>
	<th>&nbsp;</th>
	<th>ARMS Code/<br>MCode</th>
	<!--th>Manufacturer Code</th-->
	<th>Article No{if $config.link_code_name}/<br>{$config.link_code_name}{/if}</th>
	{if $config.link_code_name}
	<!--th>{$config.link_code_name}</th-->
	{/if}
	<th width=40%>SKU</th>
	{if $form.sku_type eq 'CONSIGN'}
	<th align=center>Price Type</th>
	{/if}
	<th align=center>Selling Price</th>
	<th>Return Type</th>
	<th nowrap>Inv / DO<br />No.</th>
	<th nowrap>Inv / DO<br />Date</th>
	{if $form.sku_type ne 'CONSIGN' && !$config.gra_checklist_hide_cost}
	<th>Cost</th>
	{/if}
	<th>Qty (pcs)</th>
	{if !$config.gra_checklist_hide_cost}
		<th>Amount</th>
		{if $form.is_under_gst}
			<th>GST</th>
			<th>Amount<br /> Incl. GST</th>
		{/if}
	{/if}
</tr>

{assign var=t_page value=0}
{assign var=gra_items value=$items}

{section name=i loop=$gra_items}
<!--{$t_page++}-->
{assign var=total_qty value=$gra_items[i].qty+$total_qty}
{if $form.sku_type eq 'CONSIGN'}
	{assign var=row_amt value=$gra_items[i].qty*$gra_items[i].selling_price}
{else}
	{assign var=row_amt value=$gra_items[i].qty*$gra_items[i].cost}
{/if}
{assign var=total_amt value=$total_amt+$row_amt|round:2}
{if $gra_items[i].doc_allow_decimal}
	{assign var=qty_dp value=$config.global_qty_decimal_points}
{else}
	{assign var=qty_dp value=0}
{/if}
<tr id="tbrow_{$gra_items[i].id}" bgcolor="{cycle values="#eeeeee,"}" class="no_border_bottom">
	{if !$page_item_info[i].not_item}
		<td align=right>
		<!--{$line_no++}-->
		{$gra_items[i].item_no+1}
		</td>
		<td>{$gra_items[i].sku_item_code}<br>{$gra_items[i].mcode}</td>
		<!--td>{$gra_items[i].mcode|default:"&nbsp;"}</td-->
		
		<td>{$gra_items[i].artno|default:"&nbsp;"}{if $config.link_code_name}<br>{$gra_items[i].link_code}{/if}</td>
		{if $config.link_code_name}
		<!--td>{$gra_items[i].link_code|default:"&nbsp;"}</td-->
		{/if}
	{else}
		<td></td><td></td><td></td>
	{/if}
	<td>{$gra_items[i].sku}</td>
	
	{if !$page_item_info[i].not_item}
		{if $form.sku_type eq 'CONSIGN'}
			<td align=center>{$gra_items[i].price_type|default:"&nbsp;"}</th>
		{/if}
		<td align=right nowrap>
			{if $form.is_under_gst}
				{if $gra_items[i].inclusive_tax eq 'yes'}
					{$gra_items[i].gst_selling_price|number_format:2}<br/>
					<span class="gst_sp">(Excl: {$gra_items[i].selling_price|number_format:4})<span>
				{else}
					{$gra_items[i].selling_price|number_format:2}<br/>
					<span class="gst_sp">(Incl: {$gra_items[i].gst_selling_price|number_format:2})<span>
				{/if}
			{else}
				{$gra_items[i].selling_price|number_format:2}
			{/if}
		</td>
		<td>{$gra_items[i].return_type|default:"-"}</td>
		<td>{$gra_items[i].doc_no|default:"-"}</td>
		<td>{$gra_items[i].doc_date|ifzero:"-"}</td>
		{if $form.sku_type ne 'CONSIGN' && !$config.gra_checklist_hide_cost}
			<td align=right>{$gra_items[i].cost|number_format:$dp}</td>
		{/if}
		<td align=right>{$gra_items[i].qty|qty_nf}</td>
		{if !$config.gra_checklist_hide_cost}
			<td align=right>{$row_amt|number_format:2}</td>
			{if $form.is_under_gst}
				{*if $form.sku_type eq 'CONSIGN'}
					{assign var=gst_amt value=$gra_items[i].selling_price*$gra_items[i].gst_rate/100}
				{else}
					{assign var=gst_amt value=$gra_items[i].cost*$gra_items[i].gst_rate/100}
				{/if*}
				
				{assign var=row_gst value=$gra_items[i].gst}
				{assign var=ttl_gst value=$ttl_gst+$row_gst}
				{assign var=total_gst value=$total_gst+$row_gst}
				<td align="right">{$row_gst|number_format:2}</td>

				{assign var=row_gst_amt value=$gra_items[i].amount_gst}
				{assign var=total1_gst value=$total_gst1+$row_gst}
				{assign var=ttl_gst_amt value=$ttl_gst_amt+$row_gst_amt|round:2}
				
				<td align="right">{$row_gst_amt|number_format:2}</td>
			{/if}
		{/if}
	{else}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if $form.sku_type eq 'CONSIGN'}
		<td>&nbsp;</td>
		{/if}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if $form.sku_type ne 'CONSIGN' && !$config.gra_checklist_hide_cost}
		<td>&nbsp;</td>
		{/if}
		<td>&nbsp;</td>
		{if !$config.gra_checklist_hide_cost}
			<td>&nbsp;</td>
			{if $form.is_under_gst}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
		{/if}
	{/if}
</tr>
{/section}

{assign var=s2 value=$t_page}
{section name=s start=$t_page loop=9}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height=30 class="no_border_bottom {if $s2 eq 9 and !$is_lastpage}td_btm_got_line{/if}">
  <td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $form.sku_type eq 'CONSIGN'}
	<td>&nbsp;</td>
	{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $form.sku_type ne 'CONSIGN' && !$config.gra_checklist_hide_cost}
	<td>&nbsp;</td>
	{/if}
	<td>&nbsp;</td>
	{if !$config.gra_checklist_hide_cost}
		<td>&nbsp;</td>
		{if $form.is_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	{/if}
</tr>
{/section}

{if $is_lastpage}
<tr class="total_row">
	{assign var=cols value=8}
	{if $form.sku_type eq 'CONSIGN'}
		{assign var=cols value=$cols+1}		
	{/if}
	{if $form.sku_type ne 'CONSIGN' && !$config.gra_checklist_hide_cost}
		{assign var=cols value=$cols+1}
	{/if}
	
	<th colspan="{$cols}" align=right >Total</th>
	<td align=right>{$total_qty|qty_nf}</td>
	{if !$config.gra_checklist_hide_cost}
		<td align=right>{$total_amt|number_format:2}</td>
		{if $form.is_under_gst}
			<td align=right>{$ttl_gst|number_format:2}</td>
			<td align=right>{$ttl_gst_amt|number_format:2}</td>
		{/if}
	{/if}
</tr>
{/if}
</table>
<br>
{/if}


{if $new}
<h5>Items Not in ARMS SKU</h5>
<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb  xsmall">
<tr  bgcolor=#cccccc>
	<th>&nbsp;</th>
	<th>Item Code</th>
	<th width=50%>Description</th>
	<th>Return Type</th>
	<th nowrap>Inv / DO<br />No.</th>
	<th nowrap>Inv / DO<br />Date</th>
	{if !$config.gra_checklist_hide_cost}
		<th>Cost</th>
	{/if}
	<th>Qty (pcs)</th>
	{if !$config.gra_checklist_hide_cost}
		<th>Amount</th>
		{if $form.is_under_gst}
			<th>GST</th>
			<th>Amount<br /> Incl. GST</th>
		{/if}
	{/if}
</tr>

{assign var=t1_page value=0}

{section name=i loop=$new}
<!--{$t1_page++}-->
{assign var=total1_qty value=$new[i].qty+$total1_qty}
{assign var=row1_amt value=$new[i].qty*$new[i].cost}
{assign var=total1_amt value=$row1_amt+$total1_amt}
{assign var=total1_amt value=$total1_amt|round:$dp}

<tr  id="tbrow_{$new[i].id}" bgcolor="{cycle values="#eeeeee,"}" height=30 class="no_border_bottom">
    <td width=6 align=right>
    {$start_counter+$smarty.section.i.iteration}.
    </td>
	<td>{$new[i].code}</td>
	<td>{$new[i].description}</td>
	<td>{$new[i].reason|default:"-"}</td>
	<td>{$new[i].doc_no|default:"-"}</td>
	<td>{$new[i].doc_date|ifzero:"-"}</td>
	{if !$config.gra_checklist_hide_cost}
		<td align=right>{$new[i].cost|number_format:$dp}</td>
	{/if}
	<td align=right>{$new[i].qty|qty_nf}</td>
	{if !$config.gra_checklist_hide_cost}
		<td align=right>{$row1_amt|number_format:2}</td>
		{if $form.is_under_gst}
			{assign var=gst_amt value=$new[i].cost*$new[i].gst_rate/100}
			{assign var=row_gst value=$new[i].qty*$gst_amt}
			{assign var=row_gst value=$row_gst|round2}
			{assign var=ttl_gst1 value=$ttl_gst1+$row_gst}
			<td bgcolor="{$rowcolor2}" align="right">{$row_gst|number_format:2}</td>

			{assign var=row_gst_amt value=$row_gst+$row1_amt}
			{assign var=ttl_gst_amt1 value=$ttl_gst_amt1+$row_gst_amt|round:2}
			<td bgcolor="{$rowcolor2}" align="right">{$row_gst_amt|number_format:2}</td>
		{/if}
	{/if}
</tr>
{/section}

{repeat s=$t1_page+1 e=$PAGE_SIZE}
<tr height=30 class="no_border_bottom">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if !$config.gra_checklist_hide_cost}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if $form.is_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	{/if}
</tr>
{/repeat}

{if $is_lastpage}
<tr class="total_row">
	{assign var=cols value=7}
	{if $config.gra_checklist_hide_cost}
		{assign var=cols value=$cols-1}
	{/if}
	<th colspan="{$cols}" align="right">Total</th>
	<td align="right">{$total1_qty|qty_nf}</td>
	{if !$config.gra_checklist_hide_cost}
		<td align="right">{$total1_amt|number_format:2}</td>
		{if $form.is_under_gst}
			<td align="right">{$ttl_gst1|number_format:2}</td>
			<td align="right">{$ttl_gst_amt1|number_format:2}</td>
		{/if}
	{/if}
</tr>
<tr class="total_row">
	<th colspan="{$cols}" align="right">Grand Total</th>
	<td align="right">{$total1_qty+$total_qty|qty_nf}</td>
	{if !$config.gra_checklist_hide_cost}
		<td align="right">{$total1_amt+$total_amt|number_format:2}</td>
		{if $form.is_under_gst}
			<td align="right">{$ttl_gst1+$ttl_gst|number_format:2}</td>
			<td align="right">{$ttl_gst_amt1+$ttl_gst_amt|number_format:2}</td>
		{/if}
	{/if}
</tr>
{/if}
</table>
{/if}

{if $is_lastpage}
<br />
<b>Remark</b>
<div style="border:1px solid #000;padding:5px;height:{if $form.remark2}40px;{else}20px;{/if}">
{$form.remark2}
{if $form.remark}
<br />
{$form.remark}
{/if}
{if !$form.remark && !$form.remark2}
-
{/if}
</div>
{/if}

<br>

<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
<tr  bgcolor=#cccccc>
	<th width=80>&nbsp;</th>
	<th>Name</th>
	<th>Signature</th>
	<th>Date</th>
	<th>Time</th>
</tr>
<tr  height=25>
	<td><b>Issued By</b></td>
	<td align=center>{$sessioninfo.fullname}</td>
	<td>&nbsp;</td>
	<td align=center>{$smarty.now|date_format:$config.dat_format}</td>
	<td align=center>{$smarty.now|date_format:"%H:%M:%S"}</td>
</tr>
<tr  height=25>
	<td><b>Delivered By <BR>(Dept PIC)</b></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr  height=25>
	<td><b>Received By <BR>(Store Department)</b></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
</table>
</div>