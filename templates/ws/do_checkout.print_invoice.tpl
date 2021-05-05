{*
REVISION HISTORY
++++++++++++++++

1/15/2010 9:47:20 AM Andy
- Make description column to occupy as large space as it can

1/20/2010 4:17:38 PM Andy
- make top have 4 cm space, reduce space for signature, remove from_branch info, let bottom have some free space around 2cm
- remove logo

1/26/2010 3:36:15 PM Andy
- reduce to branch column space, show debtor tel and fax, also add nl2br for debtor address.

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/27/2011 5:40:09 PM Justin
- Amended the Deliver To address to take from DO when found user use Address Deliver To.

7/15/2011 3:26:59 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/9/2011 4:31:02 PM Andy
- Change all printing templates to compatible with new discount format. (show invoice discount if got)
- Add nowrap for artno for all DO printing templates.

10/7/2011 3:42:59 PM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point 

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

3/23/2015 5:23 PM Andy
- Enhance custom template to compatible with GST.

5/13/2015 10:49 AM Andy
- Enhanced to show GST Summary.

12/28/2015 3:10 PM Qiu Ying
- SKU Additional Description should show in document printing
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

{assign var=currency_multiply value=1}
{if $form.do_type eq 'transfer' and $config.consignment_modules and $config.masterfile_branch_region and $config.consignment_multiple_currency and $form.exchange_rate>1}
	{assign var=is_currency_mode value=1}
	{assign var=exchange_rate value=$form.exchange_rate}
	
	{if $form.price_indicate ne 1}
		{assign var=currency_multiply value=$currency_multiply*$exchange_rate}
	{/if}
{/if}

<!-- print sheet -->
<div class=printarea>   
<div style="height:4cm;">&nbsp;</div>

<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
	   <tr>
	       <td valign=top style="border:1px solid #000; padding:5px;width:40%">
	        {if !$form.do_branch_id && $form.open_info.name}
      		  <h4>Bill To</h4>
      			<b>{$form.open_info.name}</b><br>
      			{$form.open_info.address|nl2br}<br>
        	{elseif $form.do_type eq 'credit_sales'}
      	    <h4>Bill To</h4>
      			<b>{$form.debtor_description}</b><br>
      			{$form.debtor_address|nl2br}<br>
      			Tel: {$form.p1|default:"-"}{if $form.p2} / {$form.p2}{/if}
  		{if $form.pfax}<br>Fax: {$form.pfax}{/if}
        	{else}
      		  <h4>Deliver To</h4>
      			<b>{$to_branch.description}</b><br>
      			{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
					{$to_branch.address|nl2br}
				{else}
					{$form.address_deliver_to|nl2br}
				{/if}
				<br>
      			Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
      			{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
				{if $to_branch.gst_register_no and $form.is_under_gst}&nbsp;&nbsp;&nbsp;GST No: {$to_branch.gst_register_no}{/if}
        	{/if}
	       </td>
	       <td align=right>
      	    <table class="">
      		<tr>
            <td colspan=2>
          <div style="background:#000;padding:4px;color:#fff" align=center><b>
          {if $form.do_type eq 'open'}Cash Bill<br />{elseif $form.do_type eq 'credit_sales'}Credit Sales<br />{/if}
      INVOICE</b></div>
      		{if $copy_type ne 'normal'}<div style="text-align:right;">{$copy_type}</div>{else}{/if}
      		  </td>
          </tr>
      		<tr bgcolor="#cccccc" height=22><td nowrap>INV No.</td><td nowrap>
      		{$form.inv_no}
      		</td></tr>
      	    <tr height=22><td nowrap>INV Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
      	    <tr height=22><td nowrap>DO No.</td><td nowrap>{$form.do_no|default:"--"}</td></tr>
      		<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
      		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
      		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
      		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
      	  	</table>
      	</td>
	   </tr>
	</table>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">
<tr bgcolor=#cccccc>
	<th rowspan=2 width=5>&nbsp;</th>
	<th rowspan=2 nowrap>ARMS Code</th>
	<th rowspan=2 nowrap>Article<br>/MCode</th>
	<th rowspan=2 width="90%">SKU Description</th>
	<th rowspan=2 width=40>Price<br>(RM)</th>
	<th rowspan=2 width=40>UOM</th>
		
	{if $show_invoice}
	    <th rowspan="2" width="40">Discount</th>
	{/if}
	
	<th nowrap colspan=2 width=80>Qty</th>
	
	{* GST *}
	{if $form.is_under_gst}
		<th rowspan="2">Gross Amt</th>
		<th rowspan="2">GST Code</th>
		<th rowspan="2" width="40">GST Amt</th>
	{/if}
	
	<th rowspan=2 width=80>Total Amount {if $form.is_under_gst}Included GST{/if}<br>(RM)</th>
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>

{assign var=counter value=0}

{foreach from=$do_items key=item_index item=r name=i}
	<!-- {$counter++} -->
	<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
		<td align=center>
			{if !$page_item_info.$item_index.not_item}
				{$r.item_no+1}.
			{else}
				&nbsp;
			{/if}
		</td>
		{assign var=sku_item_code value=$r.sku_item_code}
		<td align="center">{$sku_item_code}</td>
		<td align="center" nowrap>{if $r.artno <> ''}{$r.artno}{else}{$r.mcode|default:'&nbsp;'}{/if}</td>
		<td width="90%"><div {if !$page_item_info.$item_index.no_crop}class="crop"{/if}>{$r.description}</div></td>
		
		{if !$page_item_info.$item_index.not_item}
			{assign var=cost_price value=$r.cost_price}
			
			<!-- DO Markup -->
			{if $form.do_markup_arr.0}
				{assign var=adjust_cost value=$form.do_markup_arr.0*$cost_price/100}
				{assign var=cost_price value=$cost_price+$adjust_cost}
			{/if}
			{if $form.do_markup_arr.1}
				{assign var=adjust_cost value=$form.do_markup_arr.1*$cost_price/100}
				{assign var=cost_price value=$cost_price+$adjust_cost}
			{/if}
			
			{*assign var=p_markup value=$markup+100}
			{assign var=p_markup value=$p_markup/100}
			{assign var=cost_price value=`$p_markup*$cost_price`*}
			
			<td align="right">{$cost_price/$r.uom_fraction|number_format:$config.global_cost_decimal_points}</td>
			<td align=center>{$r.uom_code|default:"EACH"}</td>

			{* assign var=amt_ctn value=$cost_price*$r.ctn}
			{assign var=amt_pcs value=$cost_price/$r.uom_fraction*$r.pcs}
			{assign var=total_row value=$amt_ctn+$amt_pcs|round2}

			{if $show_invoice}
				{get_discount_amt assign=discount_amt amt=$total_row discount_pattern=$r.item_discount currency_multiply=$currency_multiply}
				{assign var=total_row value=$total_row-$discount_amt}
				<td align="right">{$r.item_discount|default:'-'}</td>
			{/if*}
			
			{if $show_invoice}
				<td align="right">{$r.item_discount|default:'-'}</td>
			{/if}

			<td align="right">{$r.ctn|qty_nf}</td>
			<td align="right">{$r.pcs|qty_nf}</td>

			{if $show_invoice}
				{if $form.is_under_gst}
					<td align="right">{$r.inv_line_gross_amt|number_format:2}</td>
					<td align="center">{$r.gst_code}({$r.gst_rate}%)</td>
					<td align="right">{$r.inv_line_gst_amt|number_format:2}</td>
				{/if}
		
				<td align="right">{$r.inv_line_amt|number_format:2}</td>
			{else}
				{if $form.is_under_gst}
					<td align="right">{$r.line_gross_amt|number_format:2}</td>
					<td align="center">{$r.gst_code}({$r.gst_rate}%)</td>
					<td align="right">{$r.line_gst_amt|number_format:2}</td>
				{/if}
				
				<td align="right">{$r.line_amt|number_format:2}</td>
			{/if}
			
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
				<td>&nbsp;</td>
			{/if}
			{if $show_invoice}<td>&nbsp;</td>{/if}
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
		<td>&nbsp;</td>
	{/if}
	{if $show_invoice}<td>&nbsp;</td>{/if}
</tr>
{/section}

{if $is_lastpage}
	{* Sub Total *}
	{assign var=cols value=8}
	{if $show_invoice}{assign var=cols value=$cols+1}{/if}
	
	<tr class="total_row">
		<th align="right" colspan="{$cols}">Sub Total</th>
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
			<th align="right">{$form.sub_total_inv_amt|number_format:2}</th>
		{else}
			<th align="right">{$form.sub_total_amt|number_format:2}</th>
		{/if}
	</tr>
	
	{* Discount *}
    {assign var=cols value=8}
    {if $show_invoice}{assign var=cols value=$cols+1}{/if}
	
	{if $show_invoice and $form.discount}
		{get_discount_amt assign=discount_amt amt=$total discount_pattern=$form.discount currency_multiply=$currency_multiply}
	    {assign var=total value=$total-$discount_amt}
	    <tr>
	        <th align="right" colspan="{$cols}">Discount ({$form.discount|default:'-'})</th>
			{if $form.is_under_gst}
				<th align="right">{$form.inv_sheet_gross_discount_amt|number_format:2}</th>
				<th>&nbsp;</th>
				<th align="right">{$form.inv_sheet_gst_discount|number_format:2}</th>
			{/if}
	        <th align="right">{$form.inv_sheet_discount_amt|number_format:2}</th>
	    </tr>
	{/if}
	
	{if $form.do_type eq 'open' && $config.do_enable_cash_sales_rounding && $form.total_round_inv_amt}
		{* Total Before Round *}
		{assign var=cols value=6}
		{if $show_invoice}{assign var=cols value=$cols+1}{/if}
		
		{assign var=total_b4_rounded value=$form.total_inv_amt-$form.total_round_inv_amt}
		<tr class="">
			<th align="right" colspan="{$cols}">Total Before Round</th>
			<th align="right">&nbsp;</th>
			<th align="right">&nbsp;</th>
			{if $form.is_under_gst}
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			{/if}
			<th align=right class="total_row">{$total_b4_rounded|number_format:2}</th>
		</tr>
	
		{* Rounding *}
		<tr class="">
			<th align=right colspan="{$cols}" class="total_row">Rounding</th>
			<th align=right class="total_row">&nbsp;</th>
			<th align=right class="total_row">&nbsp;</th>
			{if $form.is_under_gst}
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			{/if}
			<th align=right class="total_row">{$form.total_round_inv_amt|number_format:2}</th>
		</tr>
	{/if}
	
	{* Total After Rounded *}
	<tr class="total_row">
		{assign var=cols value=6}
		{if $show_invoice}{assign var=cols value=$cols+1}{/if}
		
		<th align="right" colspan="{$cols}" class="total_row">Total {if $form.do_type eq 'open' && $config.do_enable_cash_sales_rounding && $form.total_round_inv_amt} After Rounded{/if}</th>
		<th align="right" class="total_row">{$total_ctn|qty_nf}</th>
		<th align="right" class="total_row">{$total_pcs|qty_nf}</th>
		{if $form.is_under_gst}
			{if $show_invoice}
				<th align="right">{$form.inv_total_gross_amt|number_format:2}</th>
				<th>&nbsp;</th>
				<th align="right">{$form.inv_total_gst_amt|number_format:2}</th>
			{else}
				<th align="right">{$form.do_total_gross_amt|number_format:2}</th>
				<th>&nbsp;</th>
				<th align="right">{$form.do_total_gst_amt|number_format:2}</th>
			{/if}
		{/if}
		{if $show_invoice}
			<th align=right class="total_row">{$form.total_inv_amt|number_format:2}</th>
		{else}
			<th align=right class="total_row">{$form.total_amount|number_format:2}</th>
		{/if}
	</tr>
		
	{assign var=total value=0}
	{assign var=total_ctn value=0}
	{assign var=total_pcs value=0}
{/if}
</table>

{if $is_lastpage}
<b>Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.remark|default:"-"|nl2br}
</div>

<b>Additional Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.checkout_remark|default:"-"|nl2br}
</div>

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
