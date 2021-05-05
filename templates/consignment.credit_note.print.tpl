{*
6/8/2010 10:11:36 AM Andy
- CN/DN Swap

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/24/2011 4:11:01 PM Andy
- Fix missing SKU description in CN/DN printing.

7/15/2011 11:33:37 AM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

1/21/2015 10:46 AM Justin
- Enhanced to have GST calculation.

3/14/2015 1:11 PM Justin
- Bug fixed on total gross discount did not calculate correctly.

4/16/2015 10:05 AM Justin
- Enhanced to remove lost and over items wording.

5/19/2015 2:34 PM Justin
- Enhanced to have company no.

10/15/2015 11:36 AM DingRen
- Enhance to show foreign amount if to branch is foreign branch

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

10/7/2016 9:48 AM Andy
- Change invoice no. and invoice date to cn/dn no and cn/dn date.
*}

{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
{if ($sheet_type eq 'cn' and $config.cn_printing_no_item_line) or ($sheet_type eq 'dn' and $config.dn_printing_no_item_line)}
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


<!-- print sheet -->
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td width=100%>
	<h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2>
	{$from_branch.address|nl2br}<br>
	Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
	{if $from_branch.phone_3}
	&nbsp;&nbsp; Fax: {$from_branch.phone_3}
	{/if}
	{if $config.enable_gst and $from_branch.gst_register_no}
		<br />GST No: {$from_branch.gst_register_no}
	{/if}
	</td>
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
			<div style="background:#000;padding:4px;color:#fff" align=center>
				{if $sheet_type eq 'cn'}
					<b>CREDIT NOTE</b>
				{else}
				    <b>DEBIT NOTE</b>
				{/if}
			</div>
		<br>
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>{if $sheet_type eq 'cn'}CN{else}DN{/if} No.</td><td nowrap>{$form.inv_no}</td></tr>
	    <tr height=22><td nowrap>{if $sheet_type eq 'cn'}CN{else}DN{/if} Date</td><td nowrap>{$form.date|date_format:$config.dat_format}</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
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
</tr>
</table>

<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">

<tr bgcolor=#cccccc >
	<th rowspan=2 width=5>&nbsp;</th>
	<th rowspan=2 width=50>ARMS Code</th>
	<th rowspan=2 width=50>Article /<br>MCode</th>
	<th rowspan=2>SKU Description</th>
	<th rowspan=2 width=40>Selling<br>({$to_branch.currency_code|default:'RM'})</th>
	<th rowspan=2 width=40>Price<br>({$to_branch.currency_code|default:'RM'})</th>
	<th rowspan=2 width=40>UOM</th>
	<th nowrap colspan=2 width=80>Qty</th>
	<th rowspan=2 width=80>Amount<br>({$to_branch.currency_code|default:'RM'})</th>
	<th rowspan=2 width=60>%</th>
	<th rowspan=2 width=80>NET</th>
	{if $form.is_under_gst}
		<th rowspan=2 width=80>GST</th>
		<th rowspan=2 width=80>Net<br />Incl. GST</th>
	{/if}
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>
{assign var=counter value=0}

{foreach name=i from=$items item=r key=item_index}
<!-- {$counter++} -->
<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	{if !$page_item_info.$item_index.not_item}
		{if $to_branch.currency_code eq 'RM'}
			{assign var=cost value=$r.cost_price}
			{assign var=total_row value=$r.item_amt+$r.discount_amt}
			{assign var=item_gst value=$r.item_gst}
			{assign var=item_gst_amt value=$r.item_gst_amt}
			{assign var=net value=$r.item_amt}
		{else}
			{assign var=cost value=$r.foreign_cost_price|number_format:3:".":""}
			{assign var=total_row value=$r.item_foreign_amt+$r.foreign_discount_amt}
			{assign var=item_gst value=$r.item_foreign_gst}
			{assign var=item_gst_amt value=$r.item_foreign_gst_amt}
			{assign var=net value=$r.item_foreign_amt}
		{/if}
		{assign var=total_row value=$total_row|round2}
		<td align=center>{$r.item_no+1}.</td>{*<td align=center>{$start_counter+$counter}.</td>*}
		<td align=center>{$r.sku_item_code}</td>
		<td nowrap>{$r.artno_mcode|default:"-"}</td>
	{else}
		<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	{/if}
	<td><div class="crop">{$r.sku_description}</div></td>
	{if !$page_item_info.$item_index.not_item}
		<td align="right">{$r.selling_price|number_format:2}</td>
		<td align="right">{$cost|number_format:2}</td>
		<td align=center>{$r.uom_code|default:"EACH"}</td>
		<td align="right">{$r.ctn}</td>
		<td align="right">{$r.pcs}</td>
		<td align="right">{$total_row|number_format:2}</td>
		<td align="right">{$r.discount_per|ifzero:'&nbsp;':'%'}</td>
		<td align="right">{$net|number_format:2}</td>
		{if $form.is_under_gst}
			<td align="right">{$item_gst|number_format:2}</td>
			<td align="right">{$item_gst_amt|number_format:2}</td>
			{assign var=total_gst value=$total_gst+$item_gst}
			{assign var=total_gst_amt value=$total_gst_amt+$item_gst_amt}
		{/if}
		{assign var=total value=$total+$total_row}
		{assign var=total_net value=$total_net+$net}
		{assign var=total_ctn value=$r.ctn+$total_ctn}
		{assign var=total_pcs value=$r.pcs+$total_pcs}
	{else}
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
  {assign var=cols value=5}
  <td rowspan="3" colspan="{$cols}">Remark<br />{$form.remark|default:"-"|nl2br}</td>
  <td align=right colspan="2" nowrap>Sub Total</td>
  <td>&nbsp;</td><td>&nbsp;</td>
  <td align=right>{$total|number_format:2}</td>
  	<td>&nbsp;</td>
  	<td align=right>{$total_net|number_format:2}</td>
	{if $form.is_under_gst}
		<td align=right>{$total_gst|number_format:2}</td>
		<td align=right>{$total_gst_amt|number_format:2}</td>
	{/if}
</tr>
{if $to_branch.currency_code eq 'RM'}
	{assign var=gross_discount_amount value=$form.gross_discount_amount}
	{assign var=sheet_gst_discount value=$form.sheet_gst_discount}
	{assign var=discount_amount value=$form.discount_amount}
	{assign var=total_gross_amt value=$form.total_gross_amt}
	{assign var=total_gst_amt value=$form.total_gst_amt}
	{assign var=total_amount value=$form.total_amount}
{else}
	{assign var=gross_discount_amount value=$form.gross_foreign_discount_amount}
	{assign var=sheet_gst_discount value=$form.sheet_foreign_gst_discount}
	{assign var=discount_amount value=$form.foreign_discount_amount}
	{assign var=total_gross_amt value=$form.total_foreign_gross_amt}
	{assign var=total_gst_amt value=$form.total_foreign_gst_amt}
	{assign var=total_amount value=$form.total_foreign_amount}
{/if}
<tr>
	<td align=right colspan="2" nowrap>Discount ({$form.discount|default:'0'}%)</td>
	<td colspan="4">&nbsp;</td>
	{if $form.is_under_gst}
		<td align=right>{$gross_discount_amount|number_format:2}</td>
		<td align=right>{$sheet_gst_discount|number_format:2}</td>
	{/if}
	<td align=right>{$discount_amount|number_format:2}</td>
</tr>
<tr>
  	<td align="right" colspan="2">Total</td>
	<td align=right>{$total_ctn}</td>
	<td align=right>{$total_pcs}</td>
	<td colspan="2">&nbsp;</td>
	{if $form.is_under_gst}
		<td align=right>{$total_gross_amt|number_format:2}</td>
		<td align=right>{$total_gst_amt|number_format:2}</td>
	{/if}
	<td align=right>{$total_amount|number_format:2}</td>
  	
</tr>
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

</div>
