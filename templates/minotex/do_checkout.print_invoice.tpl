{*
12/21/2009 12:11:48 PM Andy
- Add additional feature to allow user using customized templates and row num in Cash Bill DO

1/26/2010 1:24:56 PM Andy
- Amount under total not BOLD now

3/24/2010 10:01:25 AM Andy
- Change font size larger for branch info, sheet info and total row.
- Add wording at print invoice.

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

10/18/2010 4:49:04 PM Justin
- make the ARMS and Mcode bigger font as Description.

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

7/15/2011 2:44:11 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/10/2011 3:09:32 PM Andy
- Add nowrap for artno for all DO printing templates.

10/7/2011 3:42:59 PM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point 

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

3/23/2015 3:14 PM Andy
- Enhance custom template to compatible with GST.

5/13/2015 10:24 AM Andy
- Enhanced to show GST Summary.

12/28/2015 3:10 PM Qiu Ying
- SKU Additional Description should show in document printing

4/26/2017 8:58 AM Khausalya 
- Enhanced changes from RM to use config setting. 

5/8/2017 9:51 AM Khausalya
- Enhanced changes from Ringgit Malaysia to use config setting. 

7/20/2017 16:57 Qiu Ying
- Bug fixed on artno/mcode not show if the items is open item

8/28/2018 11:56 AM Justin
- Enhanced to show/hide Tax Invoice caption base on document's GST status.
*}

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
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}

{if $form.do_type eq 'transfer' and $config.do_transfer_have_discount}
	{assign var=show_invoice value=1}
{elseif $form.do_type eq 'open' and $config.do_cash_sales_have_discount}
    {assign var=show_invoice value=1}
{elseif $form.do_type eq 'credit_sales' and $config.do_credit_sales_have_discount}
    {assign var=show_invoice value=1}
{/if}

<!-- print sheet -->
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="" style="margin-bottom:1px;">
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
		{if $config.enable_gst and $from_branch.gst_register_no}
			 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
			 {if $form.is_under_gst}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="xlarge">Tax Invoice</span>{/if}
		{/if}
		
	</span>
	</td>
	<td rowspan=2 align=right>
	    <table>
		<tr><td colspan=2>
<div style="background:#000;color:#fff;" align=center><b>
    	Cash Bill<br />
		INVOICE</b></div>
		{if $copy_type ne 'normal'}<div style="text-align:right;">{$copy_type}</div>{/if}
		</td></tr>
		<tr bgcolor="#cccccc" height=20><td nowrap>INV No.</td><td nowrap>
		{$form.inv_no}
		</td></tr>
		<tr height=20><td nowrap>DO NO</td><td nowrap>{$form.do_no}</td></tr>
	    <tr height=20><td nowrap>INV Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		<tr height=20><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
		<tr bgcolor="#cccccc" height=20><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=20><td colspan=2 align=center>{$page}</tr>
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
    <table width=100% height="80px" style="border:1px solid #000;border-collapse:collapse;">
		<tr>
			<td valign=top style="border:1px solid #000;">
			<h4>Bill To</h4>
			<b>{$form.open_info.name}</b><br>
			{$form.open_info.address}<br>
			</td>
		</tr>
	</table>
</td>
</tr>
</table>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="small">
<tr bgcolor="#cccccc" class="got_top got_btm">
	<th rowspan=2 width=5 class="got_left got_right">&nbsp;</th>
	<th rowspan=2 width="" nowrap class="got_right">ARMS Code</th>
	<th rowspan=2 width="" nowrap class="got_right">Article<br>/MCode</th>
	<th rowspan=2 class="got_right" width="90%">SKU Description</th>
	<th rowspan=2 width="40" class="got_right remain">Price<br>({$config.arms_currency.symbol})</th>
	<th rowspan=2 width="40" class="got_right remain">UOM</th>
	
	{if $show_invoice}
	    <th rowspan="2" width="40" class="got_right">Inv. Discount</th>
	{/if}
	
	<th nowrap colspan=2 width="80" class="got_right remain">Qty</th>
	
	{* GST *}
	{if $form.is_under_gst}
		<th rowspan="2" class="got_right">Gross Amt</th>
		<th rowspan="2" class="got_right">GST Code</th>
		<th rowspan="2" class="got_right" width="40">GST Amt</th>
	{/if}
	
	<th rowspan=2 width="50" class="got_right remain">Total<br>Amount {if $form.is_under_gst}Included GST{/if}<br>({$config.arms_currency.symbol})</th>
</tr>

<tr bgcolor="#cccccc" class="got_btm remain1">
	<th nowrap width=40 class="got_right">Ctn</th>
	<th nowrap width=40 class="got_right">Pcs</th>
</tr>
<tbody class="tbody_container">
{assign var=counter value=0}
{foreach from=$do_items key=item_index item=r name=i}
	<!-- {$counter++} -->
	<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
		<td align="center" class="got_left">
			{if !$page_item_info.$item_index.not_item}
				{$r.item_no+1}.
			{else}
				&nbsp;
			{/if}
		</td>
		{assign var=sku_item_code value=$r.sku_item_code}
		<td align="center" class="s2">{$sku_item_code}</td>
		<td align="center" class="s2" nowrap>
			{if $r.oi}
				{$r.artno_mcode|default:'&nbsp;'}
			{else}
				{if $r.artno}{$r.artno}{else}{$r.mcode|default:'&nbsp;'}{/if}
			{/if}
		</td>
		<td width="90%"><div {if !$page_item_info.$item_index.no_crop}class="crop"{/if}>{$r.description}</div></td>
		
		{if !$page_item_info.$item_index.not_item}
			{assign var=cost_price value=$r.cost_price}
			
			{* DO Markup *}
			{if $form.do_markup_arr.0}
				{assign var=adjust_cost value=$form.do_markup_arr.0*$cost_price/100}
				{assign var=cost_price value=$cost_price+$adjust_cost}
			{/if}
			{if $form.do_markup_arr.1}
				{assign var=adjust_cost value=$form.do_markup_arr.1*$cost_price/100}
				{assign var=cost_price value=$cost_price+$adjust_cost}
			{/if}
			
			
			{* assign var=p_markup value=$markup+100}
			{assign var=p_markup value=$p_markup/100}
			{assign var=cost_price value=`$p_markup*$cost_price`*}
			
			<td align="right" class="remain xlarge">{$cost_price/$r.uom_fraction|number_format:$config.global_cost_decimal_points}</td>
			<td align="center" class="remain xlarge">{$r.uom_code|default:"EACH"}</td>

			{* assign var=amt_ctn value=$cost_price*$r.ctn}
			{assign var=amt_pcs value=$cost_price/$r.uom_fraction*$r.pcs}
			{assign var=total_row value=$amt_ctn+$amt_pcs|round2 *}
		
			{if $show_invoice}
				<td align="right">{$r.item_discount|default:'-'}</td>
			{/if}
				
			<td align="right" class="remain xlarge">{$r.ctn|qty_nf}</td>
			<td align="right" class="remain xlarge">{$r.pcs|qty_nf}</td>
			
			{if $show_invoice}
				{if $form.is_under_gst}
					<td align="right">{$r.inv_line_gross_amt|number_format:2}</td>
					<td align="center">{$r.gst_code}({$r.gst_rate}%)</td>
					<td align="right">{$r.inv_line_gst_amt|number_format:2}</td>
				{/if}
		
				<td align="right" class="got_right xlarge">{$r.inv_line_amt|number_format:2}</td>
			{else}
				{if $form.is_under_gst}
					<td align="right">{$r.line_gross_amt|number_format:2}</td>
					<td align="center">{$r.gst_code}({$r.gst_rate}%)</td>
					<td align="right">{$r.line_gst_amt|number_format:2}</td>
				{/if}
				
				<td align="right" class="got_right xlarge">{$r.line_amt|number_format:2}</td>
			{/if}
			
			{assign var=total_ctn value=$r.ctn+$total_ctn}
			{assign var=total_pcs value=$r.pcs+$total_pcs}
		{else}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			
			{if $show_invoice}<td>&nbsp;</td>{/if}
			
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			
			{if $form.is_under_gst}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
			
			<td class="got_right xlarge">&nbsp;</td>
		{/if}
	</tr>
{/foreach}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height=20 class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td class="got_left">&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	
	{if $show_invoice}<td>&nbsp;</td>{/if}
	
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	
	<td class="got_right xlarge">&nbsp;</td>
</tr>
{/section}

{if $is_lastpage}
	{* Sub Total *}
	{assign var=cols value=8}
	{if $show_invoice}{assign var=cols value=$cols+1}{/if}
	
	<tr class="total_row">
		<th align="right" colspan="{$cols}" class="got_left">Sub Total</th>
		{if $form.is_under_gst}
			{if $show_invoice}
				<th align="right">{$form.inv_sub_total_gross_amt|number_format:2}</th>
				<th>&nbsp;</th>
				<th align="right">{$form.inv_sub_total_gst_amt|number_format:2}</th>
			{else}
				<th align="right">{$form.sub_total_gross_amt|number_format:2}</th>
				<th>&nbsp;</th>
				<th align="right">{$form.sub_total_gst_amt|number_format:2}</th>
			{/if}
		{/if}
		{if $show_invoice}
			<th align="right" class="got_right">{$form.sub_total_inv_amt|number_format:2}</th>
		{else}
			<th align="right" class="got_right">{$form.sub_total_amt|number_format:2}</th>
		{/if}
	</tr>
	
	{* Discount *}
    {assign var=cols value=8}
    {if $show_invoice}{assign var=cols value=$cols+1}{/if}
	
	{if $show_invoice and $form.discount}
		{get_discount_amt assign=discount_amt amt=$total discount_pattern=$form.discount currency_multiply=$currency_multiply}
	    {assign var=total value=$total-$discount_amt}
	    <tr>
	        <th align="right" colspan="{$cols}" class="got_left">Discount ({$form.discount|default:'-'})</th>
			{if $form.is_under_gst}
				<th align="right">{$form.inv_sheet_gross_discount_amt|number_format:2}</th>
				<th>&nbsp;</th>
				<th align="right">{$form.inv_sheet_gst_discount|number_format:2}</th>
			{/if}
	        <th align="right" class="got_right">{$form.inv_sheet_discount_amt|number_format:2}</th>
	    </tr>
	{/if}
	
	{if $form.do_type eq 'open' && $config.do_enable_cash_sales_rounding && $form.total_round_inv_amt}
		{* Total Before Round *}
		{assign var=cols value=6}
		{if $show_invoice}{assign var=cols value=$cols+1}{/if}
		
		{assign var=total_b4_rounded value=$form.total_inv_amt-$form.total_round_inv_amt}
		<tr class="">
			<th align="right" colspan="{$cols}" class="got_left">Total Before Round</th>
			<th align="right">&nbsp;</th>
			<th align="right">&nbsp;</th>
			{if $form.is_under_gst}
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			{/if}
			<th align=right class="total_row got_right">{$total_b4_rounded|number_format:2}</th>
		</tr>
	
		{* Rounding *}
		<tr class="">
			<th align=right colspan="{$cols}" class="total_row got_left">Rounding</th>
			<th align=right class="total_row">&nbsp;</th>
			<th align=right class="total_row">&nbsp;</th>
			{if $form.is_under_gst}
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			{/if}
			<th align=right class="total_row got_right">{$form.total_round_inv_amt|number_format:2}</th>
		</tr>
	{/if}
	
	{assign var=cols value=6}
	{if $show_invoice}{assign var=cols value=$cols+1}{/if}
	<tr class="total_row got_btm">
		<th colspan="{$cols}" class="got_left got_right xlarge" align="left">
			{if $show_invoice}
				{assign var=total value=$form.total_inv_amt}
			{else}
				{assign var=total value=$form.total_amount}
			{/if}
			{$config.arms_currency.name} {convert_number number=$total show_decimal=1 print=1} Only
			<div style="text-align:right;float:right;" class="">
			<b>Total</b>
			</div>
		</th>
		<th align=right class="total_row  got_right xlarge" style="font-weight:normal;">{$total_ctn|qty_nf}</th>
		<th align=right class="total_row  got_right xlarge" style="font-weight:normal;">{$total_pcs|qty_nf}</th>
		
		{if $form.is_under_gst}
			{if $show_invoice}
				<th align="right" class="got_right xlarge" style="font-weight:normal;">{$form.inv_total_gross_amt|number_format:2}</th>
				<th>&nbsp;</th>
				<th align="right" class="got_right xlarge" style="font-weight:normal;">{$form.inv_total_gst_amt|number_format:2}</th>
			{else}
				<th align="right" class="got_right xlarge" style="font-weight:normal;">{$form.do_total_gross_amt|number_format:2}</th>
				<th>&nbsp;</th>
				<th align="right" class="got_right xlarge" style="font-weight:normal;">{$form.do_total_gst_amt|number_format:2}</th>
			{/if}
		{/if}
		<th align=right class="total_row got_right xlarge" style="font-weight:normal;">{$total|number_format:2}</th>
	</tr>
	{assign var=total value=0}
	{assign var=total_ctn value=0}
	{assign var=total_pcs value=0}
{/if}
</tbody>
</table>

{if $is_lastpage}

{if $form.is_under_gst}
	{include file='gst_summary.tpl' gst_summary_list=$form.gst_summary.inv.gst}
{/if}

{if $config.invoice_footer}<p class=small>{$config.invoice_footer}</p>{/if}

<table width=100%>
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
