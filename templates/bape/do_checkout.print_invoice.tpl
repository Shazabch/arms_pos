{*
12/7/2020 4:24 PM Andy
- Changed company_no to have register icon.

12/16/2020 11:18 AM Andy
- Added padding between logo and address.
- Fixed sku additional description row.

12/22/2020 12:10 PM Andy
- Changed to show MCode, Old Code instead of Old Code, Art No.

12/22/2020 12:43 PM Andy
- Changed to show Old Code then only MCode.
- Changed to display cost_price as 2 decimal.
*}
{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
{literal}
.td_btm_got_line td,.td_btm_got_line th{
    border-bottom:1px solid black !important;
}
.no_border_bottom td{
	border-bottom:none !important;
}
.total_row{
	background-color: black;
	color: white;
}
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
.th{
	
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
				<td><h2>{$from_branch.description} {if $from_branch.company_no}&reg; {$from_branch.company_no}{/if}</h2></td>
			</tr>
			{/if}
			<tr>
				<td>{$from_branch.address}</td>
			</tr>
			<tr>
				<td>Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}</td>
			</tr>
		</table>
	</td>
{else}
	<td>
		{if !$config.do_print_hide_company_logo}
			{if $alt_logo_img}
				<img src="{$alt_logo_img}" height="80" hspace="5" vspace="5" />
			{else}
				<img src="{get_logo_url mod='do'}" height="80" hspace="5" vspace="5" />
			{/if}
		{else}
		&nbsp;
		{/if}
	</td>
	<td width="100%" style="padding-left:50px;">
		<h2>{$from_branch.description} {if $from_branch.company_no}&reg; {$from_branch.company_no}{/if}</h2>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
	</td>
{/if}
</tr>
<tr>
	<td colspan="2">
		<table width="100%" cellspacing="5" cellpadding="0" border="0" height="120px">
			<tr>
				<td valign="top" width="32%" style="padding:1%">
					<h4>Bill to</h4>
					{if $form.do_type ne 'transfer'}
						{if $form.do_type eq 'credit_sales'}
							<b>{$form.debtor_description}</b><br>
							{$form.debtor_address|nl2br}<br>
							Tel: {$form.debtor_phone|default:'-'}<br>
						{else}
							<b>{$form.open_info.name}</b><br>
							{$form.open_info.address|nl2br}<br />
						{/if}
					{else}
						<b>{$to_branch.description}</b><br>
						{$to_branch.address|nl2br}<br>
						Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
					{/if}
				</td>

				<td valign="top" width="32%" style="padding:1%">
					<h4>Ship to</h4>
					{if $form.do_type ne 'transfer'}
						{if $form.do_type eq 'credit_sales'}
							<b>{$form.deliver_debtor_description|default:$form.debtor_description}</b><br>
							{$form.debtor_deliver_address|nl2br}<br>
							Tel: {$form.deliver_debtor_phone|default:$form.debtor_phone|default:'-'}<br>
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
					{/if}
				</td>
				<td valign="top" width="32%" style="padding:1%">
				<table>
					<tr>
						<td><h4>Invoice Number</h4></td>
						<td>&nbsp;<b>{$form.inv_no}</b></td>
					</tr>
					<tr>
						<td><h4>Date</h4></td>
						<td>&nbsp;<b>{$form.do_date|date_format:$config.dat_format}</b></td>
					</tr>
					{if $form.do_type ne 'transfer'}
					<tr>
						<td><h4>Customer Name</h4></td>
						<td>&nbsp;<b>{if $form.do_type eq 'credit_sales'}{$form.debtor_description}{else}{$form.open_info.name}{/if}</b></td>
					</tr>
					{/if}
				</table>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<table border="0' cellspacing="0" cellpadding="4" width="100%" style="border-collapse: collapse;">
<tr style="background-color: black; color: white;">
	<th rowspan=1 align="left">QTY</th>
	<th rowspan=1 width="70%">ITEM</th>
	<th rowspan=1 align="right">PRICE</th>
	{if $invoice_has_discount}<th rowspan=1 align="right">DISCOUNT</th>{/if}
	<th rowspan=1 align="right" nowrap>ITEM TOTAL ({$config.arms_currency.symbol})</th>
</tr>
{assign var=counter value=0}
{foreach from=$do_items key=item_index item=r name=i}
{assign var=custom_pcs value=$r.uom_fraction*$r.ctn+$r.pcs}
	<!-- {$counter++} -->
	<tr style="background: #eaeaea;" class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
		<td align="left">{if !$page_item_info.$item_index.not_item}{$custom_pcs|qty_nf}{/if}</td>
		<td width="70%" align="left">
			{$r.description}
			{if !$page_item_info.$item_index.not_item}
				- {$r.size|default:"-"}/{$r.color|default:"-"}<br>
				{if $r.link_code neq '' || $r.mcode neq ''}({$r.link_code|default:"-"}, {$r.mcode|default:"-"}){/if}
			{/if}
		</td>
		
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
			
			<td align="right">{$cost_price/$r.uom_fraction|number_format:2}</td>
			{if $invoice_has_discount}<td align="right">{$r.item_discount|default:'-'}</td>{/if}
			<td align="right">{$r.inv_line_amt|number_format:2}</td>		
		{else}
			<td>&nbsp;</td>
			{if $invoice_has_discount}<td>&nbsp;</td>{/if}
			<td>&nbsp;</td>
		{/if}
	</tr>
{/foreach}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height="20" style="background: #eaeaea;">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $invoice_has_discount}<td>&nbsp;</td>{/if}
</tr>
{/section}

{if $is_lastpage}
	{assign var=cols value=1}
	{if $invoice_has_discount}{assign var=cols value=$cols+1}{/if}
	
	{if $form.discount || $form.inv_sheet_adj_amt || ($form.do_type eq 'open' && $config.do_enable_cash_sales_rounding && $form.total_round_inv_amt)}
	<tr>
		<th align="right"></th>
		<th align="right"></th>
		<th align="right" colspan="{$cols}" class="total_row" nowrap>Sub Total {$config.arms_currency.symbol}</th>
		<th align="right" class="total_row" nowrap>{$form.sub_total_inv_amt|number_format:2}</th>
	</tr>
	{/if}
	
	{* Discount *}
	{if $form.discount}
		{get_discount_amt assign=discount_amt amt=$total discount_pattern=$form.discount currency_multiply=$currency_multiply}
	    {assign var=total value=$total-$discount_amt}
	    <tr>
			<th align="right"></th>
			<th align="right"></th>
			<th align="right" colspan="{$cols}" class="total_row" nowrap>Discount {$config.arms_currency.symbol}</th>
	        <th align="right" class="total_row" nowrap>{$form.inv_sheet_discount_amt|number_format:2}</th>
	    </tr>
	{/if}
	
	{* Invoice Amount Adjustment *}
	{if $form.inv_sheet_adj_amt}
		 <tr>
			<th align="right"></th>
			<th align="right"></th>
			<th align="right" colspan="{$cols}" class="total_row" nowrap>Invoice Amount Adjust {$config.arms_currency.symbol}</th>
	        <th align="right" class="total_row" nowrap>{$form.inv_sheet_adj_amt|number_format:2}</th>
	    </tr>
	{/if}

	{* Total Before Round *}
	{if $form.do_type eq 'open' && $config.do_enable_cash_sales_rounding && $form.total_round_inv_amt}
		{assign var=total_b4_rounded value=$form.total_inv_amt-$form.total_round_inv_amt}
		<tr>
			<th align="right"></th>
			<th align="right"></th>
			<th align="right" colspan="{$cols}" class="total_row" nowrap>Total Before Round {$config.arms_currency.symbol}</th>
			<th align="right" class="total_row" nowrap>{$total_b4_rounded|number_format:2}</th>
		</tr>
		
		{* Rounding *}
		<tr>
			<th align="right"></th>
			<th align="right"></th>
			<th align="right" colspan="{$cols}" class="total_row" nowrap>Rounding {$config.arms_currency.symbol}</th>
			<th align="right" class="total_row" nowrap>{$form.total_round_inv_amt|number_format:2}</th>
		</tr>
	{/if}

	{* Total After Rounded *}
	<tr>
		<th align="right"></th>
		<th align="right"></th>
		<th align="right" colspan="{$cols}" class="total_row" nowrap>Total {$config.arms_currency.symbol}</th>
		<th align="right" class="total_row" nowrap>{$form.total_inv_amt|number_format:2}</th>
	</tr>
		
	{assign var=total value=0}
	{assign var=total_ctn value=0}
	{assign var=total_pcs value=0}
{/if}
</table>
{if $is_lastpage}<br><h4 align="center">Thank you for your business.</h4>{/if}
</div>
