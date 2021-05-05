{*
4/26/2017 8:35 PM Khausalya
- Enhanced changes from RM to use config setting. 
*}

<table width="100%" style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border small body" cellspacing="1" cellpadding="1" id="docs_items">

{* header *}
<thead>
<tr bgcolor=#ffffff>
	<th rowspan=2 width=20>#</th>
	<th nowrap rowspan=2 width=100>ARMS Code</th>
	<th nowrap rowspan=2 width=80>Article /<br>MCode</th>
	<th nowrap rowspan=2>SKU Description</th>

	<th rowspan=2 width=60>Selling Price<br>({$config.arms_currency.symbol})</th>
	<th rowspan=2 width=80>UOM</th>
	<th nowrap colspan="2">Qty</th>
	<th rowspan=2 width=60>Total<br>Qty</th>
	<th rowspan="2" width="60">Discount <b>[<a href="javascript:void(show_discount_help());">?</a>]</b></th>
	<th rowspan=2 width=60>Total Amount<BR>({$config.arms_currency.symbol})</th>
	
</tr>
<tr bgcolor=#ffffff>
    <th nowrap width="60"><span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span></th>
	<th nowrap width="60"><span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span></th>
</tr>
</thead>

{* body *}
<tbody id="tbody_item_list">
	{foreach from=$form.item_list item=item name=f}
	    {include file='dp.sales_order.open.sheet.item_row.tpl'}
	{/foreach}
</tbody>

{* footer *}
<tfoot id="tbl_footer">
	{assign var=show_sub_total value=0}
	{if $form.sheet_discount_amount}
		{assign var=show_sub_total value=1}
	{/if}
	<!-- Sub Total -->
	<tr bgcolor="#ffffff" class="normal" height="24" id="tr_sub_total" style="{if !$show_sub_total}display:none;{/if}height:24px;">
		{assign var=colspan value=8}
	    <td colspan="{$colspan}" nowrap class="r"><b>Sub Total</b></td>
	    {assign var=sub_total_amt value=$form.total_amount+$form.sheet_discount_amount}
	    <th class="r" id="td_sub_total_amount" colspan="3"><span id="span_sub_total_amount">{$sub_total_amt|number_format:2:".":""}</span></th>
	</tr>

	<!-- Sheet Discount -->
	<tr bgcolor="#ffffff" class="normal" id="tr_sheet_discount" style="{if !$show_sub_total}display:none;{/if}height:24px;">
		{assign var=colspan value=8}
	    <td colspan="{$colspan}" nowrap align="right" id="td_sheet_discount"><b>Discount (<span id="span_sheet_discount">{$form.sheet_discount|ifzero:''}</span>)</b></td>
	    <th class="r" id="td_sheet_discount_amount" colspan="3"><span id="span_sheet_discount_amount">{$form.sheet_discount_amount*-1|default:0|number_format:2:".":""}</span></th>
	</tr>

	<!-- Total -->
    <tr bgcolor="#ffffff" class="normal" height="24">
        {assign var=colspan value=8}

        <td colspan="{$colspan}" nowrap align="right"><b>Total</b></td>
        <td width="80">
			<b>
			T.Ctn : <span id="span_total_ctn">{$form.total_ctn|default:$total_ctn|qty_nf}</span><br>
			T.Pcs : <span id="span_total_pcs">{$form.total_pcs|default:$total_pcs|qty_nf}</span>
			</b>
		</td>
		<th class="r" colspan="2">
			<span id="span_total_amt">
				{$form.total_amount|default:0|number_format:2:".":""}
			</span>
		</th>
    </tr>
</tfoot>

</table>