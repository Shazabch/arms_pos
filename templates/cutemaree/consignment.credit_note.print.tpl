{*
6/8/2010 10:11:36 AM Andy
- CN/DN Swap

6/11/2010 11:39:57 AM Andy
- Remove footer word.

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/24/2011 4:11:01 PM Andy
- Fix missing SKU description in CN/DN printing.

7/15/2011 1:15:56 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

4/4/2012 10:54:00 AM Andy
- Change email address.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

1/21/2015 10:46 AM Justin
- Enhanced to have GST calculation.

6/22/2015 6:00 PM Andy
- Add GST ID.

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

4/20/2017 10:36 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/22/2017 1:47 PM Justin
- Enhanced company address to use from Masterfile Branch instead of HARDCODED it.
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
.header_tbl td{
    border:1px solid black;
	padding:3px;
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


<!-- print sheet -->
<div class=printarea>
<div style="text-align:center;" class="small">
	<img src="/cutemaree/cutemaree.jpg" height="70" /><br />
	<b>(Co No.. {$from_branch.company_no})</b><br />
	{$from_branch.address}<br />
	GST No: {$from_branch.gst_register_no} &nbsp;&nbsp;&nbsp; Tel: {$from_branch.phone_1|default:'-'}, {$from_branch.phone_2|default:'-'} &nbsp;&nbsp;&nbsp; Fax: {$from_branch.phone_3|default:'-'} &nbsp;&nbsp;&nbsp; E-mail: {$from_branch.contact_email}<br />
	<h2>{if $sheet_type eq 'cn'}CREDIT NOTE{else}DEBIT NOTE{/if}</h2>
</div>
<table class="header_tbl" cellspacing="10px" cellpadding="4" width=100%>
	<tr>
	    <!-- Bill To-->
	    <td width="50%" class="small">
	        	<h3>&nbsp;&nbsp;&nbsp;Bill To</h3>
	        	<b>{$to_branch.description}</b><br>
				{$to_branch.address|nl2br}<br>
				Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
				{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
	    </td>
	    <td width="30%" border="1" class="small">
	            Date: <b> {$form.date|date_format:"%d/%m/%Y"} </b><br /><br />
	            Department: <b>{$to_branch.con_dept_name|default:'-'}</b><br /><br />
	            Your/Our Purchase<br />
	            Order No: <b>CONSIGNMENT</b>
	    </td>
	    <td width="20%">
	        Invoice No: <b>{$form.inv_no}</b><br />

	        Terms: <b>{$to_branch.con_terms|default:'-'}</b><br />
	        {$page}
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
	<th rowspan=2 width=40>Selling<br>({$config.arms_currency.symbol})</th>
	<th rowspan=2 width=40>Price<br>({$config.arms_currency.symbol})</th>
	<th rowspan=2 width=40>UOM</th>
	<th nowrap colspan=2 width=80>Qty</th>
	<th rowspan=2 width=80>Amount<br>({$config.arms_currency.symbol})</th>
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
		<td align=center>{$r.item_no+1}.</td>{*<td align=center>{$start_counter+$counter}.</td>*}
		<td align=center>{$r.sku_item_code}</td>
		<td nowrap>{$r.artno_mcode|default:"-"}</td>
	{else}
		<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	{/if}
	<td><div class="crop">{$r.sku_description}</div></td>
	
	{if !$page_item_info.$item_index.not_item}
		<td align="right">{$r.selling_price|number_format:2}</td>
		<td align="right">{$r.cost_price|number_format:2}</td>
		<td align=center>{$r.uom_code|default:"EACH"}</td>
		<td align="right">{$r.ctn}</td>
		<td align="right">{$r.pcs}</td>

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
  	<td align=right>{$total2|number_format:2}</td>
	{if $form.is_under_gst}
		<td align=right>{$total_gst|number_format:2}</td>
		<td align=right>{$total_gst_amt|number_format:2}</td>
	{/if}
</tr>
<tr>
	{assign var=discount_amount value=0}
	{assign var=total_discount_amount value=0}
	{*if $form.disc_arr.0}
	    {assign var=discount_per value=$form.disc_arr.0*0.01}
		{assign var=discount_amount value=$total*$discount_per}
		{assign var=total_discount_amount value=$total_discount_amount+$discount_amount}
		{assign var=total value=$total-$discount_amount}
	{/if}
	{if $form.disc_arr.1}
	    {assign var=discount_per value=$form.disc_arr.1*0.01}
		{assign var=discount_amount value=$total*$discount_per}
		{assign var=total_discount_amount value=$total_discount_amount+$discount_amount}
		{assign var=total value=$total-$discount_amount}
	{/if*}
	<td align=right colspan="2" nowrap>Discount ({$form.discount|default:'0'}%)</td>
	<td colspan="4">&nbsp;</td>
	{*<td align=right>{$total_discount_amount|number_format:2}</td>*}
	{if $form.is_under_gst}
		<td align=right>{$form.gross_discount_amount|number_format:2}</td>
		<td align=right>{$form.sheet_gst_discount|number_format:2}</td>
	{/if}
	<td align=right>{$form.discount_amount|number_format:2}</td>
</tr>
{if $form.discount_amount}{assign var=actual_total value=$total2-$form.discount_amount}{/if}
<tr>
  	<td align="right" colspan="2">Total</td>
	<td align=right>{$total_ctn}</td>
	<td align=right>{$total_pcs}</td>
	<td colspan="2">&nbsp;</td>
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
</div>
