{*
6/10/2010 6:22:48 PM Andy
- Make item line's word bigger.

12/15/2011 2:57:22 PM Justin
- Added new columns for foreign amt and price.
- Added span for selling price for currency code.
- Added total for foreign.

1/21/2015 5:55 PM Justin
- Enhanced to have GST calculation.
*}

{literal}
<style>
input[disabled],input[readonly],select[disabled], textarea[disabled]{
  color:black;
}
</style>
{/literal}

<table width="100%" style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing="1" cellpadding="1" id="docs_items">

<!--START HEADER-->
<thead class="small">
<tr bgcolor="#ffffff">
	<th rowspan="2" width="20">#</th>
	<th nowrap rowspan="2" width="100">ARMS Code</th>
	<th nowrap rowspan="2" width="80">Article /<br />MCode</th>
	<th nowrap rowspan="2">SKU Description</th>
	<th colspan="2" width="100">Stock Balance</th>
	<th rowspan="2" width="60">Price<br />(RM)</th>
	{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<th rowspan="2" width="60" id="foreign_price">Price<br />(<span id="span_p_currency_code"></span>)</th>
	{/if}
	<th rowspan="2" width="60">Selling Price<br />(<span id="span_sp_currency_code">RM</span>)</th>
	<th rowspan="2" width="80">UOM</th>
	<th nowrap colspan="2">Qty</th>
	<th rowspan="2" width="60">Total<br />Qty</th>
	<th rowspan="2" width="60">Discount</th>
	<th rowspan="2" width="60">Total Selling<br />(<span id="span_sp_amt_currency_code">RM</span>)</th>
	<th rowspan="2" width="60">Total Amount<br />(RM)</th>
	{if $form.is_under_gst}
		<th rowspan="2">GST Code</th>
		<th rowspan="2">GST Amount</th>
		<th rowspan="2">Total Amount<br />Include GST<br />(RM)</th>
	{/if}
	{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<th rowspan="2" width="60" id="foreign_ttl_amt">Total Amount<br />(<span id="span_amt_currency_code"></span>)</th>
	{/if}
</tr>
<tr bgcolor=#ffffff>
    <th nowrap width="50">From</th>
    <th nowrap width="50">To</th>
    <th nowrap width="60"><span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span></th>
	<th nowrap width="60"><span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span></th>
</tr>
</thead>
    {assign var=total_amt value=0}
	{assign var=total_qty value=0}
	{assign var=total_ctn value=0}
	{assign var=total_pcs value=0}
<tbody id="tbody_container">
	{foreach from=$items item=item name=f}
	    {include file='consignment.credit_note.open.sheet.item_row.tpl'}
	{/foreach}
</tbody>
<tfoot id="tbl_footer">
	<tr bgcolor="#ffffff" class="normal">
		<input type="hidden" name="colspan_length" value="13">
	    <td class="r" height="24" colspan="13"  id="td_sub_total"><b>Sub Total</b></td>
	    <td class="r"><span style="font-weight:bold;" id="span_subtotal_selling">{$form.total_selling|number_format:2}</td>
	    <td class="r"><span style="font-weight:bold;" id="span_subtotal_amt">{$form.total_amount|number_format:2}</td>
		{if $form.is_under_gst}
		  {assign var="total_gross_amount" value=$sub_total_amt-$form.total_gst_amt-$form.sheet_gst_discount}
		  <th></th>
		  <th class="r" id="td_sub_total_gst">{$form.total_gst_amt+$form.sheet_gst_discount|number_format:2:".":""}</th>
		  <th align=right id="td_sub_total_gst_amount">{$form.sub_total_amt|number_format:2:".":""}</th>
		{/if}
		{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
			<th align="right" id="td_subtotal_foreign_amt">{$form.sub_total_foreign_amt|number_format:2:".":""}</th>
		{/if}
	</tr>
	<tr bgcolor="#ffffff" id="tr_sheet_discount" class="normal" {if !$form.discount}style="display:none;"{/if}>
	    <td class="r" height="24" colspan="13" id="td_discount"><b>Discount(<span id="span_sheet_discount">{$form.discount|default:0}%</span>)</b></td>
	    <td class="r" colspan="2"><span id="span_discount_amt" style="font-weight:bold;">{$form.discount_amount|number_format:2}</span></td>
		{if $form.is_under_gst}
			{math assign="sheet_discount_gross_amount" equation="(x-y)*-1" x=$form.discount_amount|default:0 y=$form.sheet_gst_discount|default:0}
			<th></th>
			<th class="r" id="td_sheet_discount_gst">{$form.sheet_gst_discount*-1|number_format:2:".":""}</th>
			<th class="r" id="td_sheet_discount_gst_amount">{$sheet_discount_gross_amount|number_format:2:".":""}</th>
		{/if}
		{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
			<th align="right" id="td_display_foreign_discount_amount">
				{$form.foreign_discount_amount*-1|default:0|number_format:2:".":""}
			</th>
		{/if}
	</tr>
    <tr bgcolor="#ffffff" class="normal" height="24">
        {assign var=colspan value=12}
        <td colspan="{$colspan}" nowrap align="right" id="td_total"><b>TOTAL</b></td>
        <td width="80">
			<b>
			T.Ctn : <span id="span_total_ctn">{$form.total_ctn|default:$total_ctn}</span><br />
			T.Pcs : <span id="span_total_pcs">{$form.total_pcs|default:$total_pcs}</span>
			</b>
		</td>
		<th class="r" colspan="2">
		    <span id="span_total_amt">{$form.total_amount|default:0|number_format:2:".":""}</span>
			{if $form.is_under_gst}
			  <th></th>
			  <th class="r" id="td_total_gst">{$form.total_gst_amt|number_format:2:".":""}</th>
			  <th class="r" id="td_total_gst_amt">{$form.total_gross_amt|number_format:2:".":""}</th>
			{/if}
			{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
				<th align="right" id="display_total_foreign_amount" class="uom total_foreign_amount">
					{$form.total_foreign_amount|number_format:2:".":""}
				</th>
			{/if}
		</th>
    </tr>
</tfoot>
</table>
