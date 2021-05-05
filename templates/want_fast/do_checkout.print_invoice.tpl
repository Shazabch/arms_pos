{*
3/27/2014 3:14 PM Justin
- Bug fixed on showing DO No twice.

4/3/2014 5:47 PM Justin
- Enhanced to adjust the font size to bigger.

4/9/2014 3:13 PM Justin
- Enhanced to replace the "DO" become "INV".
- Enhanced to remove off the "Printed By".

3/23/2015 5:12 PM Andy
- Enhance custom template to compatible with GST.

5/13/2015 10:31 AM Andy
- Enhanced to show GST Summary.

12/28/2015 3:10 PM Qiu Ying
- SKU Additional Description should show in document printing
*}
{config_load file="site.conf"}
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
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td width="100%" align="center">
		<table width="1%">
			<tr>
				<td align="right">
				{if !$config.do_print_hide_company_logo}<img src="{get_logo_url mod='do'}" height=80 hspace=5 vspace=5>{else}&nbsp;{/if}
				</td>
				<td nowrap>
					<div style="font-weight:bold; font-size:28px;">{$from_branch.description}</div>
					<h4>{$from_branch.address|nl2br}<br>
					Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
					{if $from_branch.phone_3}
					&nbsp;&nbsp; Fax: {$from_branch.phone_3}
					{/if}
					{if $config.enable_gst and $from_branch.gst_register_no}
						 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
					{/if}
					{if $form.is_under_gst}<h1 align="center">Tax Invoice</h1>{/if}
					</h4>
				</td>
			</tr>
		</table>
	</td>
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
			<div style="background:#000;padding:4px;color:#fff" align=center>
				<b>{if $form.do_type eq 'open'}CASH<br />{/if}INVOICE</b>
			</div>
			{if $copy_type ne 'normal'}<div style="text-align:right;">{$copy_type}</div>{else}<br>{/if}
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>INV No.</td><td nowrap>{$form.do_no|default:"--"}</td></tr>
		<tr height=22><td nowrap>INV Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		{if $form.offline_id}
			<tr height=22 bgcolor="#cccccc"><td nowrap>Offline ID</td><td nowrap>#{$form.offline_id|string_format:"%05d"}</td></tr>
		{/if}
		<tr bgcolor="#cccccc" height=22><td nowrap>Term</td><td nowrap>&nbsp;</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</tr>
	  	</table>
	</td>
</tr>
<tr>
<td>
	{if !$form.do_branch_id && $form.open_info.name}
		<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
		<tr>
			<td valign=top style="border:1px solid #000; padding:5px">
				<h4>Sold To<br />
				{$form.open_info.name|nl2br}</h4>
			</td>	
			
		    <td valign=top style="border:1px solid #000; padding:5px">
				<h4>Deliver To<br />
				{$form.open_info.address|nl2br}</h4>
			</td>
		</tr>
		</table>
	{elseif $form.do_type eq 'credit_sales'}
	    <table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
		<tr>
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
				<h4>Sold To<br />
				{$form.debtor_description|nl2br}</h4>
			</td>
			
		    <td valign=top style="border:1px solid #000; padding:5px">
				<h4>Deliver To<br />
				{$form.debtor_address|nl2br}</h4>
			</td>
		</tr>
		</table>
	{else}
		<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
		<tr>
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
				<h4>From </h4>
				<b>{$from_branch.description}|nl2br</b><br>
				<b>{$from_branch.address|nl2br}<br>
				Tel: {$from_branch.phone_1|default:"-"}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
				{if $from_branch.phone_3}<br>Fax: {$from_branch.phone_3}{/if}
				</b>
			</td>
	
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
				<h4>Deliver To</h4>
				<b>{$to_branch.description|nl2br}</b><br>
				<b>{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
					{$to_branch.address|nl2br}
				{else}
					{$form.address_deliver_to|nl2br}
				{/if}<br>
				Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
				{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
				{if $to_branch.gst_register_no and $form.is_under_gst}&nbsp;&nbsp;&nbsp;GST No: {$to_branch.gst_register_no}{/if}
				</b>
			</td>
	
		</tr>
		</table>	
	{/if}

</td>
</tr>
</table>

<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small" style="font-weight:bold; font-size: 1em;">
<tr bgcolor=#cccccc>
	<th rowspan=2 width=5>&nbsp;</th>
	<th rowspan=2 width="90%">Description</th>
	<th rowspan=2 width=40>Size</th>
	<th rowspan=2 width=40>UOM</th>
	<th rowspan=2 width=40>Unit Price<br>(RM)</th>
	<th nowrap colspan=2 width=80>Qty</th>
	
	{if $show_invoice}
	    <th rowspan="2" width="40">Inv. Discount</th>
	{/if}
	
	{* GST *}
	{if $form.is_under_gst}
		<th rowspan="2">Gross Amt</th>
		<th rowspan="2">GST Code</th>
		<th rowspan="2" width="40">GST Amt</th>
	{/if}
	
	<th rowspan=2 width=80>Amount {if $form.is_under_gst}Included GST{/if}<br>(RM)</th>
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>

{assign var=counter value=0}

{foreach from=$do_items key=item_index item=r name=i}
	<!-- {$counter++} -->
	<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
		<td align="center">
			{if !$page_item_info.$item_index.not_item}
				{$r.item_no+1}.
			{else}
				&nbsp;
			{/if}
		</td>
		{assign var=sku_item_code value=$r.sku_item_code}
		<td width="90%"><div class="crop">{$r.description}</div></td>
		<td align="center">{$r.size|default:"-"}</td>
		
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
			{assign var=cost_price value=`$p_markup*$cost_price` *}
			
			<td align="center">{$r.uom_code|default:"EACH"}</td>
			<td align="right">{$cost_price/$r.uom_fraction|number_format:$config.global_cost_decimal_points}</td>
			<td align="right">{$r.ctn|qty_nf}</td>
			<td align="right">{$r.pcs|qty_nf}</td>
			
			{* assign var=amt_ctn value=$cost_price*$r.ctn}
			{assign var=amt_pcs value=$cost_price/$r.uom_fraction*$r.pcs}
			{assign var=total_row value=$amt_ctn+$amt_pcs|round2}
	
	        {if $show_invoice}
	            {get_discount_amt assign=discount_amt amt=$total_row discount_pattern=$r.item_discount currency_multiply=$currency_multiply}
	            {assign var=total_row value=$total_row-$discount_amt}
				<td align="right">{$r.item_discount|default:'-'}</td>
		    {/if *}
	
			{if $show_invoice}
				<td align="right">{$r.item_discount|default:'-'}</td>
		    {/if}
			
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
			{assign var=total_qty value=$total_qty+$r.ctn*$r.uom_fraction+$r.pcs}
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
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	{if $show_invoice}<td>&nbsp;</td>{/if}
</tr>
{/section}

{if $is_lastpage}
    {assign var=cols value=5}
    {if $show_invoice}{assign var=cols value=$cols+1}{/if}
	<tr class="total_row">
		<th align="left" rowspan="3" colspan="{$cols}" class="xlarge">Total Qty: {$total_qty|qty_nf}</th>
		<th align="right" colspan="2">Sub Total (RM)</th>
		
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
	
	{get_discount_amt assign=discount_amt amt=$total discount_pattern=$form.discount currency_multiply=$currency_multiply}
	{assign var=total value=$total-$discount_amt}
	<tr>
		<th align="right" colspan="2">Discount (RM)</th>
		{if $form.is_under_gst}
			<th align="right">{$form.inv_sheet_gross_discount_amt|number_format:2}</th>
			<th>&nbsp;</th>
			<th align="right">{$form.inv_sheet_gst_discount|number_format:2}</th>
		{/if}
		<th align="right">{$form.inv_sheet_discount_amt|number_format:2}</th>
	</tr>

	<tr>
		<th align="right" colspan="2" nowrap>Grand Total (RM)</th>
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
	{assign var=total_qty value=0}
{/if}
</table>

{if $is_lastpage}

{if $form.is_under_gst}
	{include file='gst_summary.tpl' gst_summary_list=$form.gst_summary.inv.gst}
{/if}

<br>

* All Cheques should and made payable to Want Fast Sdn. Bhd.<br>
* Goods sold are not returnable.<br>
* If there is any discrepancy, kindly notify within 7 days.<br>
* Otherwise, no discount or objections to be accepted.
<br><br><br>

<table width=100% style="font-weight:bold; font-size:1.2em; margin:0; padding=0;">
<tr>
<td width="50%" align="left">Received By</td>
<td width="50%" align="left">WANT FAST SDN. BHD.</td>
</tr>

<tr height="80">
<td valign="bottom" align="left" class="small">
_________________<br>
(Company Chop & Signature)<br>
Date Received:
</td>

<td valign="bottom" align="left" class="small">
_________________<br>
(Authorised Signature)<br>
&nbsp;
</td>
</tr>
</table>
{/if}
</div>
