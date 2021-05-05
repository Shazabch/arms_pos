{*
4/25/2017 1:47 PM Khausalya 
- Enhanced changes from RM to use config setting.

6/15/2017 16:19 Qiu Ying
- Bug fixed on stock balance should be labelled with Branch Code, not currency symbol

7/20/2017 08:33 AM Qiu Ying
- Enhanced to use the artno and mcode in sku item table instead of artno_mcode in do_items and po_items
- Bug fixed on artno/mcode not show if the items is open item
*}


<table width=100% style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing=1 cellpadding=1>

<!--START HEADER-->
<thead class=small>
<tr bgcolor=#ffffff>
	<th nowrap rowspan=2 width=100>ARMS Code</th>
	<th nowrap rowspan=2 width=80>Article /<br />MCode</th>
	<th nowrap rowspan=2>SKU Description</th>
	{if $form.create_type==3}
	<th rowspan=2  width=60>PO Cost<br />(<span id="span_poc_currency_code">{$config.arms_currency.symbol}</span>)</th>
	{/if}
	{assign var=colspan value=2}

	{if $do_type eq 'credit_sales' || $do_type eq 'open'}{assign var=colspan value=$colspan-1}{/if}
	<th colspan="{$colspan}"  width=60>Stock Balance</th>
	{if $config.show_parent_stock_balance}
		<th colspan="{$colspan}"  width=60>Parent Stock Balance</th>
	{/if}
	{if $sessioninfo.privilege.SHOW_COST}
		<th rowspan=2 width=60>Cost</th>
	{/if}
	{if $do_type eq 'credit_sales' && $sessioninfo.privilege.SHOW_COST}
		<th rowspan=2 width=60>Price<br />Indicator</th>
	{/if}
	<th rowspan=2 width=60>Price<br />({$config.arms_currency.symbol})</th>
	{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
		<th rowspan=2 width=60 id="foreign_price">Price<br />(<span id="span_p_currency_code">{$config.arms_currency.symbol}</span>)</th>
	{/if}
	<th rowspan=2 width=80>Master<br />UOM</th>
	<th rowspan=2 width=80>DO UOM</th>
	{if !$form.open_info.name}
	<th rowspan=2 width=60>Selling<br />Price<br />({$config.arms_currency.symbol})</th>
	{/if}
	<th nowrap>Qty</th>
	{if $do_type eq 'transfer' and $config.do_use_rcv_pcs}
	    <th rowspan="2" width="80">Rcv Qty</th>
	{/if}
	<th rowspan=2 width=60>Total<br />Qty</th>
	
	{if $form.is_under_gst}
		<th rowspan="2">Gross<br />Amount</th>
		<th rowspan="2">GST Code</th>
	{/if}

	<th rowspan=2 width=60>Total Amount {if $form.is_under_gst}Included GST{/if}<br />({$config.arms_currency.symbol})</th>
	{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
		<th rowspan=2 width=60 id="foreign_ttl_amt">Total Amount<br />(<span id="span_amt_currency_code"></span>)</th>
	{/if}
	{if $show_discount}
	    <th rowspan="2" width="60">Invoice<br />Discount
			<b>[<a href="javascript:void(show_discount_help());">?</a>]</b>
		</th>
		{if $form.is_under_gst}
			<th rowspan="2">Gross Invoice Amount</th>
			<th rowspan="2">Invoice GST</th>
		{/if}
		<th rowspan="2" width="60">Invoice<br />Amount {if $form.is_under_gst}Included GST<br/>{/if}({$config.arms_currency.symbol})</th>
		{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
			<th rowspan="2" width="60" id="foreign_inv_amt">Invoice<br />Amount (<span id="span_inv_amt_currency_code"></span>)</th>
		{/if}
	{/if}

	{if $have_scan_items}
		<th rowspan=2 width=60>Scanned<br />Qty</th>
		<th rowspan=2 width=60>Variance</th>
	{/if}
</tr>
<tr bgcolor=#ffffff>
	<th>(<span id="span_branch_code1">{$branch_list[$form.branch_id].code}</span>)</th>
	{*<script>change_branch_code_for_stock_balance1();</script>*}
	
	{if $do_type ne 'credit_sales' && $do_type ne 'open'}
	<th>(<span id="span_branch_code2">{$branch_list[$form.do_branch_id].code}</span>)</th>
	{*<script>change_branch_code_for_stock_balance2();</script>*}
	{/if}
	
	{if $config.show_parent_stock_balance}
		<th>(<span id="span_parent_branch_code1">{$branch_list[$form.branch_id].code}</span>)</th>
	
		{if $do_type eq 'transfer'}
			<th>(<span id="span_parent_branch_code2">{$branch_list[$form.do_branch_id].code}</span>)</th>
		{/if}
	{/if}
<th align=center>
	<span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span> 
	<span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span>
</th>
</tr>
</thead>
{foreach from=$do_items item=item name=fitem}
<tr bgcolor="#ffee99">
	<td align=center>{$item.sku_item_code}</td>
<td nowrap>
	{if $item.oi}
		{$item.artno_mcode}
	{else}
		{if $item.artno}{$item.artno}{else}{$item.mcode}{/if}
	{/if}
</td>
	<td>
	{if $config.do_auto_split_by_price_type}
		<sup style="color:blue" class="span_price_type_{$item.sku_item_id}">{$item.price_type.$bid}</sup>
	{/if}
	{$item.description}
	</td>

{if $form.do_type eq 'transfer' and $config.consignment_modules and $config.masterfile_branch_region and $config.consignment_multiple_currency and $form.exchange_rate>1}
	{assign var=is_currency_mode value=1}
	{assign var=exchange_rate value=$form.exchange_rate}
{/if}

{assign var=bid value=$form.do_branch_id|default:"0"}

{if $form.create_type==3}
	<td align=right>
	<input class="r cost uom" value="{$item.po_cost|number_format:$config.global_cost_decimal_points:'.':''}" size=6 readonly>
	</td>
{/if}
	<td align=center>{if !$item.oi}<input size=5 class="small r stock_balance_1_{$item.sku_item_id}" readonly value="{$item.stock_balance1}" style="background:#ddd;">{/if}</td>
	{if $do_type ne 'credit_sales' && $do_type ne 'open'}
		<td align=center>{if !$item.oi}<input size=5 class="small r stock_balance_2_{$item.sku_item_id}" readonly value="{$item.stock_balance2}" style="background:#ddd;">{/if}</td>
	{/if}
	
	{if $config.show_parent_stock_balance}
		<td align=center>{if !$item.oi}<input size=5 class="small r stock_balance_1_{$item.sku_item_id}" readonly value="{$item.parent_stock_balance1|default:'0'}" style="background:#ddd;">{/if}</td>
		{if $do_type eq 'transfer'}
			<td align=center>{if !$item.oi}<input size=5 class="small r stock_balance_2_{$item.sku_item_id}" readonly value="{$item.parent_stock_balance2|default:'0'}" style="background:#ddd;">{/if}</td>
		{/if}
	{/if}

	<!-- Latest Cost -->
	<td class="r" {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}><input class='r' style="background:#ddd;" size=6 value="{$item.cost|number_format:$config.global_cost_decimal_points:'.':''}" readonly /></td>

	<!-- Price indicator -->
	<td align="center" class="pindicator" {if $do_type ne 'credit_sales' || !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>
		<span>{if !$item.price_no_history && $do_type eq 'credit_sales' && !$form.no_use_credit_sales_cost}
				Credit Sales DO
			  {else}{$item.price_indicator}{/if}
		</span>
	</td>

<!-- Price -->
<td align="right">
	{assign var=cost value=$item.cost_price|default:$item.grn_cost|default:$item.po_cost|default:$item.master_cost}

	{assign var=foreign_cost value=$item.foreign_cost_price|number_format:$config.global_cost_decimal_points:'.':''}

	{if $item.price_indicator eq 'Last DO' || $item.price_indicator eq 'Cost'}
		{assign var=cost_decimal_points value=$config.global_cost_decimal_points}
	{else}
		{assign var=cost_decimal_points value=2}
	{/if}

	{assign var=display_cost_price value=$item.display_cost_price|round:4}
	{if $display_cost_price<=0}{assign var=display_cost_price value=$cost|round:4}{/if}
	{if $form.is_under_gst}
		<div style="white-space: nowrap;" class="small">
			<span>
				<input size="8" name="display_cost_price[{$item.id}]" class="{if $item.price_no_history}price_no_history{/if}"
				readonly value="{$display_cost_price|number_format:4:'.':''}" />
			</span>
		</div>
	{/if}

	<div style="{if $form.is_under_gst && !$item.display_cost_price_is_inclusive}display:none;{/if}">
		<input class="r cost uom {if $item.price_no_history}price_no_history{/if}" value="{if $form.is_under_gst}{$cost}{else}{$cost|number_format:$cost_decimal_points:'.':''}{/if}"
		size="6" readonly type="{if $form.is_under_gst}hidden{else}text{/if}">
		<span class="small" style="color:blue;{if !$form.is_under_gst}display:none;{/if}">{$cost|number_format:4:'.':''}</span>
	</div>
</td>

{if $config.consignment_modules && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
	<td align=right class="foreign_cost_price" {if $hide_currency_field}style="display:none;"{/if}>
	<input class="r cost uom {if $item.price_no_history}price_no_history{/if}" value="{$foreign_cost}" size=6 readonly>
	</td>
{/if}

<td align="center">
{$item.master_uom_code|default:'EACH'}
</td>

{if ((!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom) && !$readonly}
	{assign var=uom_fraction value=1}
	{assign var=uom_id value=1}
{else}
	{assign var=uom_fraction value=$item.uom_fraction}
	{assign var=uom_id value=$item.uom_id}
{/if}

<td align="center">
{section name=i loop=$uom}
{if $uom_id == $uom[i].id or ($uom_id==0 and $uom[i].code eq 'EACH')}{$uom[i].code}{/if}
{/section}
</td>


{if !$form.open_info.name}
<td align="right">{if !$item.oi}
<input size=5 value="{$item.selling_price|default:'-'|number_format:2:'.':''}" class="r selling_price_{$item.sku_item_id}" readonly style="background:#ddd;">
{/if}</td>
{/if}

{assign var=qty value=0}
{if $form.do_markup}
	{if $form.do_markup_arr.0}
	    {assign var=do_markup_per value=$form.do_markup_arr.0/100+1}
		{assign var=cost value=$cost*$do_markup_per}
		{assign var=foreign_cost value=$foreign_cost*$do_markup_per}
	{/if}
	{if $form.do_markup_arr.1}
	    {assign var=do_markup_per value=$form.do_markup_arr.1/100+1}
		{assign var=cost value=$cost*$do_markup_per}
		{assign var=foreign_cost value=$foreign_cost*$do_markup_per}
	{/if}
{/if}

{assign var=total_qty value=0}
{assign var=qty value=$qty+$item.ctn*$uom_fraction+$item.pcs}

{if $cost ne 0 && $uom_fraction ne 0}
	{assign var=$cost value=$cost/$uom_fraction}
{/if}
{if $foreign_cost ne 0 && $uom_fraction ne 0}
	{assign var=foreign_cost value=$foreign_cost/$uom_fraction}
{/if}

{* assign var=amount value=$cost*$qty *}

{assign var=tmp_amount value=$cost*$item.ctn}
{assign var=tmp_amount2 value=0}
{if $cost ne 0 && $uom_fraction ne 0}
	{assign var=tmp_amount2 value=$item.pcs/$uom_fraction*$cost}
{/if}

{assign var=gross_amount value=$tmp_amount+$tmp_amount2}
{assign var=gst_amt value=0}
{if $form.is_under_gst}
	{assign var=gst_amt value=$gross_amount*$item.gst_rate/100}
	{*{$gst_amt}<br/>*}
{/if}

{assign var=amount value=$gross_amount+$gst_amt}
{*{$amount}<br/>*}
{* assign var=foreign_amount value=$foreign_cost*$qty *}

{assign var=tmp_amount value=$foreign_cost*$item.ctn}
{assign var=tmp_amount2 value=0}
{if $foreign_cost ne 0 && $uom_fraction ne 0}
	{assign var=tmp_amount2 value=$item.pcs/$uom_fraction*$foreign_cost}
{/if}
{assign var=foreign_cost value=$tmp_amount+$tmp_amount2}

{assign var=amount value=$amount|round:2}
{assign var=foreign_amount value=$foreign_amount|round:2}
{$ttl_foreign_amount}
<!--input type=hidden name=do_item_id[{$item.id}] value="{$item.id}"-->

<td align=center nowrap>
<input class="r uom inp_qty_ctn" {if $uom_fraction == 1 or $uom_id==1 or !$uom_id}value="-"{else}value="{$item.ctn}"{/if} style="width:{if $item.doc_allow_decimal}40{else}30{/if}px;" size=1 readonly>

<input class="r uom inp_qty_pcs" style="width:{if $item.doc_allow_decimal}40{else}30{/if}px; background:#fc9;" size=1 value="{$item.pcs}" readonly>
</td>

{if $do_type eq 'transfer' and $config.do_use_rcv_pcs}
	<td align="center">
		<input class="r uom" style="width:{if $item.doc_allow_decimal}40{else}30{/if}px; " size=1 value="{$item.rcv_pcs}" readonly>
	</td>
{/if}
<td align=right>
	<span class="uom">{$qty|default:0}</span>
</td>


{if $form.is_under_gst}
	{* GST Gross Amt *}
	<td class="r">
		<span>{$gross_amount|number_format:2}</span>
	</td>

	{* GST Selection *}
	<td align="center">
		{foreach from=$gst_list item=gst}
		{if $item.gst_id eq $gst.id and $item.gst_code eq $gst.code and $item.gst_rate eq $gst.rate}
			{$gst.code} ({$gst.rate}%)
		{/if}
		{/foreach}
		<br />
		<span class="small" style="color:blue;">
			{$gst_amt|number_format:2}
		</span>
	</td>
{/if}
<td align=right>
	<span class="uom row_amt">{$amount|default:0|number_format:2:".":""}</span>
</td>
{if $config.consignment_modules && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
	<td align=right class="foreign_amt" {if $hide_currency_field}style="display:none;"{/if}>
		<span class="uom row_foreign_amt">{$foreign_amount|default:0|number_format:2:".":""}</span>
	</td>
{/if}

{if $item.item_discount}
	{assign var=currency_multiply value=1}
	{if $is_currency_mode and $exchange_rate>0 and $form.price_indicate ne 1}
		{assign var=currency_multiply value=$currency_multiply*$exchange_rate}
	{/if}
	{get_discount_amt assign=discount_amt amt=$gross_amount discount_pattern=$item.item_discount currency_multiply=$currency_multiply}

	<!-- Foreign -->
	{assign var=currency_multiply value=1}
	{if $is_currency_mode and $exchange_rate>0 and $form.price_indicate eq 1}
		{assign var=currency_multiply value=$currency_multiply/$exchange_rate}
	{/if}
	{get_discount_amt assign=discount_foreign_amt amt=$foreign_amount discount_pattern=$item.item_discount currency_multiply=$currency_multiply}
{else}
    {assign var=discount_amt value=0}
{/if}

{assign var=gross_inv_amt value=$gross_amount-$discount_amt}
{assign var=gross_inv_amt value=$gross_inv_amt}
{assign var=inv_gst_amt value=0}
{if $form.is_under_gst}
	{assign var=inv_gst_amt value=$gross_inv_amt*$item.gst_rate/100}
{/if}

{assign var=row_invoice_amt value=$gross_inv_amt+$inv_gst_amt}

{assign var=row_invoice_foreign_amt value=$foreign_amount-$discount_foreign_amt}

{if $show_discount}
	<td class="r">
		<input readonly class="r" value="{$item.item_discount}" size="10" />
	</td>

	{if $form.is_under_gst}
		{* Gross Invoice Amount *}
		<td class="r">
			<span>{$gross_inv_amt|number_format:2}</span>
		</td>

		{* Invoice GST *}
		<td class="r">
			<span>{$inv_gst_amt|number_format:2}</span>
		</td>
	{/if}

	<td class="r"><span>{$row_invoice_amt|number_format:2}</span></td>

	{if $config.consignment_modules && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
		<td align="right" class="foreign_inv_amt" {if $hide_currency_field}style="display:none;"{/if}>
			<span>{$row_invoice_foreign_amt|number_format:2}</span>
		</td>
	{/if}
{/if}

{if $have_scan_items}
	<td class="r">{if isset($item.scan_qty)}{$item.scan_qty|qty_nf}{else}-{/if}</td>
	<td class="r {if $item.variance > 0}pv{elseif $item.variance<0}nv{/if}">
		{if isset($item.scan_qty)}
			{if $item.variance>0}+{elseif $item.variance<0}-{/if}{$item.variance|qty_nf}
		{else}
			-
		{/if}
	</td>
{/if}
</tr>
{/foreach}
</thead>
</table>

