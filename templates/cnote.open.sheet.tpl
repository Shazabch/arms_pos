{*
4/25/2017 1:49 PM Khausalya 
- Enhanced changes from RM to use config setting. 

5/29/2017 16:50 Qiu Ying
- Enhanced to return multiple invoice

06/22/2020 04:26 PM Sheila
- Fixed table boxes alignment and width.
*}

<table width="100%" style="border:1px solid #999; padding:5px; background-color:#fe9" cellspacing="1" cellpadding="1" id="tbl_items">
	{* Header *}
	<thead>
		<tr bgcolor="#ffffff">
			<th rowspan="2" width="20">#</th>
			<th nowrap rowspan="2" width="100">ARMS Code</th>
			<th nowrap rowspan="2" width="80">Article /<br>MCode</th>
			<th nowrap rowspan="2">SKU Description</th>
			
			{if $sessioninfo.privilege.SHOW_COST}
				<th rowspan="2" width="60">Cost<br>({$config.arms_currency.symbol})</th>
			{/if}
			<th rowspan="2" width="60">Price<br>({$config.arms_currency.symbol})</th>
			<th rowspan="2" width="80">UOM</th>
			<th nowrap colspan="2" width="40">Qty</th>
			<th rowspan="2">Total<br>Qty</th>
			
			{if $form.is_under_gst}
				<th rowspan="2">Gross<br />Amount</th>
				<th rowspan="2">GST Code</th>
				<th rowspan="2">GST Amt</th>
			{/if}

			<th rowspan="2" width="60">Total Amount {if $form.is_under_gst}Included GST{/if}<BR>({$config.arms_currency.symbol})</th>
			
			<th rowspan="2" width="100">Reason</th>
			
			{if !$form.do_id}
				<th rowspan="2">Invoice No</th>
				<th rowspan="2">Invoice Date</th>
			{/if}
		</tr>
		<tr bgcolor="#ffffff">
			<th nowrap align="center"><input class="tbl_col_ctn" style="background:#fff;border:1px solid #ccc;width: 40px;text-align:center;font-size: 10px;font-weight: bold;padding-right: 2px;cursor: context-menu;" value="Ctn" disabled=""></th>
			<th nowrap align="center"><input class="tbl_col_ctn" style="background:#fc9;border:1px solid #ccc;width: 40px;text-align:center;font-size: 10px;font-weight: bold;padding-right: 2px;cursor: context-menu;" class="tbl_col_pcs" value="Pcs" disabled=""></th>
		</tr>
	</thead>
	
	<tbody id="tbody_items" class="input_no_border">
		{foreach from=$items_list item=cn_item name=fcni}
			{include file='cnote.open.sheet.item_row.tpl'}
		{/foreach}
	</tbody>
	
	{* Total *}
	<tfoot>
		<tr bgcolor="#ffffff" height="24" id="row-sub_total" {if $form.discount_amt lte 0}style="display:none;"{/if}>
			{assign var=colspan value=8}
			{if $sessioninfo.privilege.SHOW_COST}{assign var=colspan value=$colspan+1}{/if}
			
			<td colspan="{$colspan}" nowrap align="right"><b>Sub Total</b></td>
			<td width="80" nowrap></td>
			{if $form.is_under_gst}
			  <th class="r"><span id="span-sub_total_gross_amount">{$form.sub_total_gross_amount|default:0|number_format:2}</span></th>
			  <th></th>
			  <th class="r"><span id="span-sub_total_gst_amount">{$form.sub_total_gst_amount|default:0|number_format:2}</span></th>
			{/if}
			<th class="r">
				<span id="span-sub_total_amount">{$form.sub_total_amount|default:0|number_format:2}</span>
			</th>
			<th>&nbsp;</th>
			{if !$form.do_id}
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			{/if}
		</tr>

		<tr bgcolor="#ffffff" height="24" id="row-discount" {if $form.discount_amt lte 0}style="display:none;"{/if}>
			{assign var=colspan value=8}
			{if $sessioninfo.privilege.SHOW_COST}{assign var=colspan value=$colspan+1}{/if}

			<td colspan="{$colspan}" nowrap align="right"><b>Discount</b></td>
			<td width="80" nowrap></td>
			{if $form.is_under_gst}
			  <th class="r"><span id="span-gross_discount_amt">{$form.gross_discount_amt|default:0|number_format:2}</span></th>
			  <th></th>
			  <th class="r"><span id="span-gst_discount_amt">{$form.gst_discount_amt|default:0|number_format:2}</span></th>
			{/if}
			<th class="r">
				<span id="span-discount_amt">{$form.discount_amt|default:0|number_format:2}</span>
			</th>
			<th>&nbsp;</th>
			{if !$form.do_id}
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			{/if}
		</tr>

		<tr bgcolor="#ffffff" height="24">
			{assign var=colspan value=8}
			{if $sessioninfo.privilege.SHOW_COST}{assign var=colspan value=$colspan+1}{/if}

			<td colspan="{$colspan}" nowrap align="right"><b>Total</b></td>			
			<td width="80" nowrap>
				<b>
					T.Ctn : <span id="span-total_ctn">{$form.total_ctn|default:0}</span><br>
					T.Pcs : <span id="span-total_pcs">{$form.total_pcs|default:0}</span>
				</b>
			</td>
			{if $form.is_under_gst}
			  <th class="r"><span id="span-total_gross_amount">{$form.total_gross_amount|default:0|number_format:2}</span></th>
			  <th></th>
			  <th class="r"><span id="span-total_gst_amount">{$form.total_gst_amount|default:0|number_format:2}</span></th>
			{/if}
			<th class="r">
				<span id="span-total_amount">
					{$form.total_amount|default:0|number_format:2}
				</span>
			</th>
			<th>&nbsp;</th>
			{if !$form.do_id}
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			{/if}
		</tr>
	</tfoot>
</table>
