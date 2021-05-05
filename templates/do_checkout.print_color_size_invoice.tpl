{*
10/29/2018 2:13 PM Justin
- Enhanced to show Branch Company Registration No. after company name.

12/28/2018 10:25 AM Justin
- Enhanced to print barcode on top of the document number.

1/16/2019 9:11 AM Justin
- Bug fixed on the slash "/" from barcode will causing the wrong result from scanner.

3/14/2019 4:47 PM Andy
- Enhanced to have "Invoice Amount Adjust".

6/19/2019 2:53 PM William
- Added new Vertical print format.
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
<div class="printarea">
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
<tr>
{if $system_settings.logo_vertical eq 1}
	<td colspan="2"  width="100%">
	<table width="100%" style="text-align: center;">
		<tr>
		{if !$config.do_print_hide_company_logo}
			{if $alt_logo_img}
			<td><img src={$alt_logo_img} height=80 hspace=5 vspace=5 style="max-width: 600px;max-height: 80px;"></td>
			{else}
			<td><img src="{get_logo_url mod='do'}" height=80 hspace=5 vspace=5 style="max-width: 600px;max-height: 80px;"></td>
			{/if}
		{else}
			<td>&nbsp;</td>
		{/if}
		</tr>
		{if $system_settings.verticle_logo_no_company_name neq 1}
		<tr>
			<td><h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2></td>
		</tr>
		{/if}
		<tr>
			<td>{$from_branch.address}</td>
		</tr>
		<tr>
			<td>
			Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
			{if $from_branch.phone_3}
			&nbsp;&nbsp; Fax: {$from_branch.phone_3}
			{/if}
			{if $config.enable_gst and $from_branch.gst_register_no}
				 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
			{/if}
			</td>
		</tr>
		<tr>
		{if $form.is_under_gst && $form.checkout}
			<td><h1 align="center">Tax Invoice</h1></td>
		{/if}
		</tr>
	</table>
	</td>
{else}
	<td>
		{if !$config.do_print_hide_company_logo}
			{if $alt_logo_img}
			<img src={$alt_logo_img} height=80 hspace=5 vspace=5>
			{else}
			<img src="{get_logo_url mod='do'}" height=80 hspace=5 vspace=5>
			{/if}
		{else}
		&nbsp;
		{/if}
		</td>
		<td width=100%>
		<h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}
		&nbsp;&nbsp; Fax: {$from_branch.phone_3}
		{/if}
		{if $config.enable_gst and $from_branch.gst_register_no}
			 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
		{/if}
		
		
		{if $form.is_under_gst && $form.checkout}
			<h1 align="center">Tax Invoice</h1>
		{/if}
	
		
	</td>
{/if}
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:4px;color:#fff" align=center><b>
    {if $form.do_type eq 'open'}Cash Bill<br />
		{elseif $form.do_type eq 'credit_sales'}Credit Sales<br />
	{/if}
	{if !$form.checkout}PROFORMA {/if}INVOICE</b>
</div>
		{if $copy_type ne 'normal'}<div style="text-align:right;">{$copy_type}</div>{else}<br>{/if}
		</td></tr>
		
			{if $form.checkout}
				<tr bgcolor="#cccccc" height=22>
					<td nowrap> INV No.</td>
					<td {if $form.approved eq 1 && $config.print_document_barcode}align="center" style="padding:0;"{/if} nowrap>
						{if $form.approved eq 1 && $config.print_document_barcode}
							<span class="barcode3of9" style="padding:0;">
								*{$form.inv_no|replace:'/':'/O'}*
							</span>
						{/if}
						
						<div {if $form.approved eq 1 && $config.print_document_barcode}style="margin-top:-5px;"{/if}>
							{$form.inv_no}
						</div>
					</td>
				</tr>
		
				{if !$config.do_printing_allow_hide_date or !$no_show_date}
					<tr height=22><td nowrap> INV Date </td>
					<td nowrap>{$form.do_date|date_format:$config.dat_format} </td></tr>
				{/if}	
			{/if}
		
	    <tr height=22><td nowrap>DO No.</td><td nowrap>{$form.do_no|default:"--"}</td></tr>
		<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		{if $form.offline_id}
			<tr height=22 bgcolor="#cccccc"><td nowrap>Offline ID</td><td nowrap>#{$form.offline_id|string_format:"%05d"}</td></tr>
		{/if}
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</tr>
	  	</table>
	</td>
</tr>
<br>
<tr>
<td colspan="2">
	<table width="100%" cellspacing="5" cellpadding="0" border="0" height="120px">
		<tr>
			<td valign="top" width="50%" style="border:1px solid #000; padding:5px">
				<h4>Bill to Address</h4>
				{if $form.do_type ne 'transfer'}
					{if $form.do_type eq 'credit_sales'}
						<b>{$form.debtor_description}</b><br>
						{$form.debtor_address|nl2br}<br>
						Tel: {$form.debtor_phone|default:'-'}<br>
						Terms: {$form.debtor_term|default:'-'}<br>
					{else}
						<b>{$form.open_info.name}</b><br>
						{$form.open_info.address|nl2br}<br />
					{/if}
				{else}
					<b>{$to_branch.description}</b><br>
					{$to_branch.address|nl2br}<br>
					Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
					{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
				{/if}
			</td>

			<td valign="top" width="50%" style="border:1px solid #000; padding:5px">
				<h4>Deliver to Address</h4>
				{if $form.do_type ne 'transfer'}
					{if $form.do_type eq 'credit_sales'}
						<b>{$form.deliver_debtor_description|default:$form.debtor_description}</b><br>
						{$form.debtor_deliver_address|nl2br}<br>
						Tel: {$form.deliver_debtor_phone|default:$form.debtor_phone|default:'-'}<br>
						Terms: {$form.deliver_debtor_term|default:$form.debtor_term|default:'-'}<br>
					{else}
						{if $form.use_address_deliver_to}
							<b>{$form.open_info.delivery_name|default:$form.open_info.name}</b><br>
							{$form.open_info.delivery_address|default:$form.open_info.address|nl2br}<br />
						{else}
							<b>{$form.open_info.name}</b><br>
							{$form.open_info.address|nl2br}<br />
						{/if}
					{/if}
				{else}
					<b>{$to_branch.description}</b><br>
					{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
						{$to_branch.address|nl2br}
					{else}
						{$form.address_deliver_to|nl2br}
					{/if}
					<br>
					Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
					{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
				{/if}
			</td>
		</tr>
	</table>
</td>
</tr>
</table>

<br>

<table border="0" cellspacing="0" cellpadding="4" width="100%" class="small">
<tr bgcolor="#cccccc" class="total_row td_btm_got_line">
	<th rowspan="2" width="5">&nbsp;</th>
	<th rowspan="2" nowrap>ARMS Code</th>
	<th rowspan="2" nowrap>Article<br>/MCode</th>
	<th rowspan="2" nowrap>Color</th>
	<th rowspan="2" width="90%">SKU Description</th>
	<th rowspan="2" width="40">Price<br>({$config.arms_currency.symbol})</th>
	<th rowspan="2" width="40">UOM</th>
	
	{if $show_invoice}
	    <th rowspan="2" width="40">Inv. Discount</th>
	{/if}
	
	<th nowrap colspan="2" width=80>Qty</th>
	
	{* GST *}
	{if $form.is_under_gst}
		<th rowspan="2">Gross Amt</th>
		<th rowspan="2">GST Code</th>
		<th rowspan="2" width="40">GST Amt</th>
	{/if}
	
	<th rowspan="2" width="80">Total Amount {if $form.is_under_gst}Included GST{/if}<br>({$config.arms_currency.symbol})</th>
</tr>

<tr bgcolor="#cccccc" class="td_btm_got_line">
	<th nowrap width="40">Ctn</th>
	<th nowrap width="40">Pcs</th>
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
		<td align="center">{$r.parent_info.sku_item_code|default:'&nbsp;'}</td>
		<td align="center" nowrap>
			{if $r.oi}	
				{$r.artno_mcode|default:'&nbsp;'}
			{else}
				{if $r.parent_info.artno}{$r.parent_info.artno|default:'&nbsp;'}{else}{$r.parent_info.mcode|default:'&nbsp;'}{/if}
			{/if}
		</td>
		<td align="center" nowrap>{$r.color|default:'N/A'}</td>
		<td width="90%"><div {if !$page_item_info.$item_index.no_crop}class="crop"{/if}>{$r.parent_info.description}</div></td>
		
		
		{if !$page_item_info.$item_index.not_item}
			<td align="right">{$r.cost_price|number_format:$config.global_cost_decimal_points}</td>
			<td align=center>{$r.uom_code|default:"EACH"}</td>
						
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
			{assign var=total_qty value=$total_qty+$r.ctn*$r.uom_fraction+$r.pcs}
		{else}
			<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
			{if $form.is_under_gst}
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
			{/if}
		{/if}
	</tr>
	
	{* Size and Color *}
	{if !$page_item_info.$item_index.not_item}
		<tr>
			<td>&nbsp;</td>
			{assign var=colspan value=6}
			{if $form.is_under_gst}
				{assign var=colspan value=$colspan+3}
			{/if}
			<td colspan="{$colspan}">
				<table>
					{* Size *}
					<tr>
						{foreach from=$r.size_list key=item_size item=item_ctn_pcs}
							<td style="padding-right:10px;">{$item_size|default:'N/A'}</td>
						{/foreach}
					</tr>
					{* Qty *}
					<tr>
						{foreach from=$r.size_list key=item_size item=item_ctn_pcs}
							<td>
								{if $item_ctn_pcs.ctn}
									{$item_ctn_pcs.ctn|qty_nf}:
								{/if}
								{$item_ctn_pcs.pcs|qty_nf}
							</td>
						{/foreach}
					</tr>
				</table>
			</td>
		</tr>
	{/if}
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
	
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	
	{if $show_invoice}<td>&nbsp;</td>{/if}
</tr>
<tr>
	<td>&nbsp;</td>
	{assign var=colspan value=6}
	{if $form.is_under_gst}
		{assign var=colspan value=$colspan+3}
	{/if}
	<td colspan="{$colspan}">
		<table>
			{* Size *}
			<tr>
				<td>&nbsp;</td>
			</tr>
			{* Qty *}
			<tr>
				<td>&nbsp;</td>
			</tr>
		</table>
	</td>
</tr>
{/section}

{if $is_lastpage}
	{* Sub Total *}
	{assign var=cols value=9}
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
    {assign var=cols value=9}
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
	
	{* Invoice Amount Adjustment *}
	{if $form.inv_sheet_adj_amt}
		<tr>
	        <th align="right" colspan="{$cols}">Invoice Amount Adjust</th>
			{if $form.is_under_gst}
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			{/if}
	        <th align="right">{$form.inv_sheet_adj_amt|number_format:2}</th>
	    </tr>
	{/if}

	{if $form.do_type eq 'open' && $config.do_enable_cash_sales_rounding && $form.total_round_inv_amt}
		{* Total Before Round *}
		{assign var=cols value=7}
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
		{assign var=cols value=7}
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

</div>
