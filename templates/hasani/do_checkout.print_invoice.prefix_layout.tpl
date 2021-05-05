{*
9/27/2011 2:32:38 PM Andy
- Amend printing layout.

10/7/2011 2:11:22 PM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point 

5:06 PM 11/2/2011 Justin
- Fixed the layout that do not fill into hanasi's prefix printed letter.
- Fixed the bugs where the row counting does not run when having more than 1 row.

1/31/2012 4:22:42 PM Andy
- Fix layout.

2/7/2012 11:13:43 AM Justin
- Fixed the missing checking of "Don't show date".

4/18/2015 12:11 PM Andy
- Enhanced to new GST Format.

5/8/2017 9:41 AM Khausalya
- Enhanced changes from Ringgit Malaysia to use config setting. 

11/16/2018 10:30 AM Justin
- Modified to match up with customer new pre-printed format (removed off GST fields).

3/4/2019 5:43 PM Justin
- Enhanced to have new pre-printed layout for Hasani.
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
body{
	#background-image:url('thumb.php?w=810&fit=1&img=templates/hasani/hasani-bg-old.jpg');
	#background-image:url('thumb.php?w=900&fit=1&img=templates/hasani/do.jpg');
	background-repeat: no-repeat;
	background-position: top left;
	width:850px;
	margin-left:10;
	margin-right:0;
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
.nowrap{
	white-space:nowrap;
}
{/literal}
</style>
<body onload="window.print()">
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
	<!-- Top: Logo and company address -->
	<div style="height:190px;border:0px solid black;">&nbsp;</div>
	
	<!-- Top: customer, deliver to and invoice info-->
	<table style="margin-left:10px; height:180px; width:850px; border:0px solid black;">
		<tr valign="top">
			<td width="240">
				<!-- Customer -->
				<div style="height:80px;font-size:90%;">
					{if $form.do_type eq 'open'}
						<b>{$form.open_info.name|nl2br}</b><br />
						{$form.open_info.address}
					{elseif $form.do_type eq 'credit_sales'}
						<b>{$form.debtor_description|nl2br}</b><br />
						{$form.debtor_address}
					{else}
						<b>{$from_branch.description|nl2br}</b><br />
						{$to_branch.address|nl2br}
					{/if}
					
				</div>
				<div style="line-height:15px;width:160px;">
					<span class="nowrap"><!-- ATTN -->ATTN: </span>
					<br />
					<span class="nowrap"><!-- TEL -->TEL:
						{if $form.do_type eq 'credit_sales'}
							{$to_debtor.phone_1}
						{else}
							{$to_branch.phone_1}
						{/if}
					</span>
					<br />
					<span class="nowrap"><!-- FAX -->FAX: 
						{if $form.do_type eq 'credit_sales'}
							{$to_debtor.phone_3}
						{else}
							{$to_branch.phone_3}
						{/if}
					</span>
					<br />
					<span class="nowrap"><!-- ACC No -->A/C No.</span>
				</div>
			</td>
			<td width="50">&nbsp;</td>
			<td width="240">
				<!-- Deliver to -->
				<div style="height:97px;font-size:90%;">
					{if $form.do_type eq 'open'}
						<b>{$form.open_info.name|nl2br}</b><br />
						{$form.open_info.address}
					{elseif $form.do_type eq 'credit_sales'}
						<b>{$form.debtor_description|nl2br}</b><br />
						{$form.debtor_address}
					{else}
						<b>{$from_branch.description|nl2br}</b><br />
						{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
							{$to_branch.address|nl2br}
						{else}
							{$form.address_deliver_to|nl2br}
						{/if}
					{/if}
				</div>
				<div style="line-height:15px;width:160px;">
					<span class="nowrap"><!-- ATTN -->ATTN: </span>
					<br />
					<span class="nowrap"><!-- TEL -->TEL:
						{if $form.do_type eq 'credit_sales'}
							{$to_debtor.phone_1}
						{else}
							{$to_branch.phone_1}
						{/if}
					</span>
				</div>
			</td>
			<td width="28">&nbsp;</td>
			<td>
				<!-- Invoice -->
				<div style="margin-top:-20px;margin-left:100px;">{if $copy_type ne 'normal'}({$copy_type}){else}&nbsp;{/if}</div>
				<div style="margin-left:150px;line-height:19px;">
					<span class="nowrap"><!-- ATTN -->{$form.inv_no}</span>
					<br />
					<span class="nowrap"><!-- PAGE -->{$page}</span>
					<br />
					<span class="nowrap">
						{if !$config.do_printing_allow_hide_date or !$no_show_date}
							<!-- DATE -->{$form.do_date|date_format:$config.dat_format}
						{else}
							&nbsp;
						{/if}
					</span>
					<div style="height:20px;"></div>
					<span class="nowrap"><!-- PO -->{$form.po_no|default:"&nbsp;"}</span>
					<br />
					<span class="nowrap"><!-- DO -->{$form.do_no|default:"&nbsp;"}</span>
					<br />
					<span class="nowrap"><!-- TERM --></span>
					<br />
					<span class="nowrap"><!-- SALESMAN --></span>
				</div>
			</td>
		</tr>
	</table>
	
	<!-- MID: ITEMS TABLE -->
	<div style="width:850px;">
		<table width="100%" border="0" style="margin-left:5px;margin-top:25px;font-size:95%;">
			{assign var=counter value=0}
			{foreach from=$do_items key=item_index item=r name=i}
				<!-- {$counter++} -->
				<tr height="20">
					{* Row No *}
					<td align="center" width="25">
						{if !$page_item_info.$item_index.not_item}
							{$r.item_no+1}.
						{else}
							&nbsp;
						{/if}
					</td>
				
					{* Art No / MCode *}
					<td nowrap width="115">{if $r.artno <> ''}{$r.artno}{else}{$r.mcode|default:'&nbsp;'}{/if}</td>
					
					{* SKU Description *}
					<td ><div class="crop">{$r.description}</div></td>
					
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
						
						{assign var=row_qty value=$r.ctn*$r.uom_fraction+$r.pcs}
						
						{* Publisher (Brand) *}
						<td align="center" width="90">
							{if !$r.brand_id}
								UNBRANDED
							{else}
								{$r.brand_code}
							{/if}
						</td>
						
						{* Qty *}
						<td align="right" width="30">{$row_qty|qty_nf}</td>
						
						{* PRICE *}
						<td align="right" width="84">{$cost_price/$r.uom_fraction|number_format:$config.global_cost_decimal_points}</td>
						
						{* Invoice Discount *}
						<td align="right" width="60">{$r.item_discount|default:'-'}</td>
						
						{* Inv Amt *}
						<td align="right" style="width:80px;">{$r.inv_line_amt|number_format:2}</td>
					{else}
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
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
				</tr>
			{/section}
			
			{if $is_lastpage}
			    {assign var=cols value=7}
				<tr>
					<td colspan="{$cols}">&nbsp;</td>
					<td style="text-align:right; margin-right:150px;line-height:40px;" align="right" class="large"><b>{$form.total_inv_amt|number_format:2}</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>
				</tr>
			{else}	
				<tr>
					<td style="line-height:40px;">&nbsp;</td>
				</tr>
			{/if}
		</table>
	</div>
	
	<div style="height:80px;padding-top:15px;">
	</div>
	
	<!-- BOTTOM -->
	<div style="margin-left:395px;margin-top:30px;"><!-- Bill checked by -->{$sessioninfo.u|default:'&nbsp;'|upper}
	</div>
</div>