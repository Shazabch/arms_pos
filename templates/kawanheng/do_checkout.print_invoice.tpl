{*
8/28/2019 9:59 AM William
- Enhanced to added new column for new config "do_custom_column".
- Enhanced do print hide "Ctn" and hide "UOM" and display "Master UOM" when config "do_checkout_invoice_alt_print_template" is using "kawanheng/do_checkout.print_invoice.tpl".

10/3/2019 3;39 PM William
- Enhanced do invoice printing to add new "shipping Details" and display "Name" and "Date" on "Approved By", "Issued By" column.
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
	<td colspan="2" width="100%">
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
<tr>
<br>
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

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">
<tr bgcolor=#cccccc>
	<th rowspan=1 width=5>&nbsp;</th>
	<th rowspan=1 nowrap>
		{if $config.replace_docs_arms_code_with_link_code}
			{$config.link_code_name|default:'Old Code'}
		{else}
			ARMS Code
		{/if}
	</th>
	<th rowspan=1 nowrap>Article<br>/MCode</th>
	<th rowspan=1 width="90%">SKU Description</th>
	<th rowspan=1 width=40>Price<br>({$config.arms_currency.symbol})</th>
	<th rowspan=1 width=40>Master UOM</th>
	
	{if $show_invoice}
	    <th rowspan="1" width="40">Inv. Discount</th>
	{/if}
	
	<th nowrap colspan=1 width=80>Pcs</th>
	
	{* GST *}
	{if $form.is_under_gst}
		<th rowspan="1">Gross Amt</th>
		<th rowspan="1">GST Code</th>
		<th rowspan="1" width="40">GST Amt</th>
	{/if}
	
	<th rowspan=1 width=80>Total Amount {if $form.is_under_gst}Included GST{/if}<br>({$config.arms_currency.symbol})</th>
	{if $config.do_custom_column.delivery_carton}
		<th rowspan="1">{$config.do_custom_column.delivery_carton.desc}</th>
	{/if}
</tr>

{assign var=counter value=0}

{foreach from=$do_items key=item_index item=r name=i}
{assign var=custom_pcs value=$r.uom_fraction*$r.ctn+$r.pcs}
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
		<td align="center">
			{if $config.replace_docs_arms_code_with_link_code}
				{$r.link_code|default:'&nbsp;'}
			{else}
				{$sku_item_code|default:'&nbsp;'}
			{/if}
		</td>
		<td align="center" nowrap>
			{if $r.oi}	
				{$r.artno_mcode|default:'&nbsp;'}
			{else}
				{if $r.artno}{$r.artno|default:'&nbsp;'}{else}{$r.mcode|default:'&nbsp;'}{/if}
			{/if}
		</td>
		<td width="90%"><div {if !$page_item_info.$item_index.no_crop}class="crop"{/if}>{$r.description}</div></td>
		
		
		{if !$page_item_info.$item_index.not_item}
		
		{assign var=row_qty value=$r.ctn*$r.uom_fraction+$r.pcs}
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
			
			{*
			{assign var=p_markup value=$markup+100}
			{assign var=p_markup value=$p_markup/100}
			
			{assign var=cost_price value=`$p_markup*$cost_price`}
			*}
			<td align="right">{$cost_price/$r.uom_fraction|number_format:$config.global_cost_decimal_points}</td>
			<td align=center>{$r.master_uom_code|default:"EACH"}</td>
			{*
			{assign var=amt_ctn value=$cost_price*$r.ctn}
			{assign var=amt_pcs value=$cost_price/$r.uom_fraction*$r.pcs}
			
			{assign var=gross_amount value=$amt_ctn+$amt_pcs}
			{assign var=gst_amt value=0}
			{if $form.is_under_gst}
				{assign var=gst_amt value=$gross_amount*$r.gst_rate/100}
			{/if}

			{assign var=total_row value=$gross_amount+$gst_amt}
			{assign var=total_row value=$total_row|round2}
			{assign var=row_gst value=$gst_amt}
			
	        {if $show_invoice}
	            {get_discount_amt assign=discount_amt amt=$gross_amount discount_pattern=$r.item_discount currency_multiply=$currency_multiply}
				
				{assign var=gross_inv_amt value=$gross_amount-$discount_amt}
				{assign var=gross_inv_amt value=$gross_inv_amt|round2}
				{assign var=inv_gst_amt value=0}
				{if $form.is_under_gst}
					{assign var=inv_gst_amt value=$gross_inv_amt*$r.gst_rate/100}
				{/if}
				{assign var=total_row value=$gross_inv_amt+$inv_gst_amt}
				{assign var=row_gst value=$inv_gst_amt}
				
				<td align="right">{$r.item_discount|default:'-'}</td>
		    {/if}
			*}
			
			{if $show_invoice}
				<td align="right">{$r.item_discount|default:'-'}</td>
		    {/if}
			
			<td align="right">{$custom_pcs|qty_nf}</td>
			
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
			{if $config.do_custom_column.delivery_carton}
				<td>{$r.custom_col.delivery_carton}</td>
			{/if}
			{*
			{assign var=total_row value=$total_row|round2}
			{assign var=total value=$total+$total_row}
			*}
			{assign var=total_ctn value=$r.ctn+$total_ctn}
			{assign var=total_pcs value=$r.pcs+$total_pcs}
			{assign var=total_qty value=$total_qty+$r.ctn*$r.uom_fraction+$r.pcs}
		{else}
			<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
			{if $config.do_custom_column.delivery_carton} 
				<td>&nbsp;</td>
			{/if}
			{if $form.is_under_gst}
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
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
	{if $config.do_custom_column.delivery_carton} 
		<td>&nbsp;</td>
	{/if}
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
	{assign var=cols value=7}
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
		{if $config.do_custom_column.delivery_carton} 
			<th></th>
		{/if}
	</tr>
	
	{* Discount *}
    {assign var=cols value=7}
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
			{if $config.do_custom_column.delivery_carton} 
				<th></th>
			{/if}
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
			{if $config.do_custom_column.delivery_carton} 
				<th></th>
			{/if}
	    </tr>
	{/if}

	{if $form.do_type eq 'open' && $config.do_enable_cash_sales_rounding && $form.total_round_inv_amt}
		{* Total Before Round *}
		{assign var=cols value=5}
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
			{if $config.do_custom_column.delivery_carton} 
				<th></th>
			{/if}
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
			{if $config.do_custom_column.delivery_carton} 
				<th></th>
			{/if}
		</tr>
	{/if}
	
	{* Total After Rounded *}
	<tr class="total_row">
		{assign var=cols value=6}
		{if $show_invoice}{assign var=cols value=$cols+1}{/if}
		<th align="right" colspan="{$cols}" class="total_row">Total {if $form.do_type eq 'open' && $config.do_enable_cash_sales_rounding && $form.total_round_inv_amt} After Rounded{/if}</th>
		<th align="right" class="total_row">{$total_qty|qty_nf}</th>
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
		{if $config.do_custom_column.delivery_carton} 
			<th></th>
		{/if}
	</tr>
		
	{assign var=total value=0}
	{assign var=total_ctn value=0}
	{assign var=total_pcs value=0}
{/if}
</table>

{if $is_lastpage}
<br>
<table width="100%">
	<tr>
		<td width="50%"><b>Remark</b></td>
		<td width="50%"><b>Additional Remark</b></td>
	</tr>
	<tr>
		<td>
			<div style="border:1px solid #000;padding:5px;height:60px;">
			{$form.remark|default:"-"|nl2br}
			</div>
		</td>
		
		<td>
			<div style="border:1px solid #000;padding:5px;height:60px;">
			{$form.checkout_remark|default:"-"|nl2br}
			</div>
		</td>
	</tr>
	<tr>
		<td><b>Shipping Details</b></td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%"  style="border:1px solid #000;padding:5px;">
				<tr>
					<td><b>Driver Name:</b> {$form.checkout_info.name}</td>
					<td><b>Lorry No:</b> {$form.checkout_info.lorry_no}</td>
					<td><b>IC No:</b> {$form.checkout_info.nric}</td>
				</tr>
				{if $form.shipment_method || $form.tracking_code}
				<tr>
					<td><b>Shipment Method:</b> {$form.shipment_method}</td>
					<td><b>Tracking Code:</b> {$form.tracking_code}</td>
				</tr>
				{/if}
			</table>
		</td>
	</tr>
</table>
{if $form.is_under_gst}
	{if $show_invoice}
		{assign var=gst_sum_list value=$form.gst_summary.inv.gst}
	{else}
		{assign var=gst_sum_list value=$form.gst_summary.do.gst}
	{/if}
	<div style="float:left; padding-right:40px;">
		{include file='gst_summary.tpl' gst_summary_list=$gst_sum_list}
	</div>
	<!--div style="float:absolute; padding-right:40px;" class="small">
		<b>
			Note:<br />
			* The GST Calculation may produce variances if comparing the physical calculation against the system calculation method.
		</b>
	</div-->
	{if $form.is_special_exemption && $form.special_exemption_rcr}
		<div class="small">
			<b>GST Relief Clause:</b><br />
			{$form.special_exemption_rcr}
		</div>
	{/if}
{/if}

{if $config.invoice_footer}
	<p class=small>{$config.invoice_footer}</p>
{else}
	<table width=100%>
	<tr height=80>

	<td valign=bottom class=small>
	_________________<br>
	Issued By<br>
	<b>Name:</b> {$form.owner_fullname}<br>
	<b>Date:</b> {if $form.confirm_timestamp > 0}{$form.confirm_timestamp|date_format:$config.dat_format}{/if}
	</td>

	<td valign=bottom class=small>
	_________________<br>
	Approved By<br>
	<b>Name:</b> {$form.approval_history_info.fullname}<br>
	<b>Date:</b> {$form.approval_history_info.timestamp|date_format:$config.dat_format}
	</td>

	<td valign=bottom class=small>
	_________________<br>
	Received By<br>
	<b>Name:</b><br>
	<b>Date:</b>
	</td>
	</tr>
	</table>
{/if}


{/if}
</div>
