{*
8/5/2011 11:58:39 AM Andy
- Add total discount and row discount.

8/8/2011 11:05:11 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

4/3/2012 4:55:58 PM Andy
- Add show relationship between PO and SO.

04/07/2016 14:00 Edwin
- Enhanced on show parent stock balance when config.show_parent_stock_balance is enabled.

4/25/2017 9:06 AM Khausalya
- Enhanced hanges from RM to use config setting. 

12/13/2018 2:48 PM Justin
- Enhanced to always show Art No and MCode in 2 columns instead of either one.

11/28/2019 9:14 AM William
- Enhance to show sku item photo when config "sales_order_show_photo" is active.

2/1/2021 10:09 AM William
- Enhance to add "Reserve Qty" column.

3/2/2021 15:30 PM Sin Rou
- Enhance to config and display out RSP and RSP Discount.
- Modify the view table in tpl by adding "RSP and RSP Discount" columns.
*}

{literal}
<style>
input[disabled],input[readonly],select[disabled], textarea[disabled]{
  color:black;
}
</style>
{/literal}

{assign var=show_reserve_qty value=0}
{if (!$form.status && !$form.active && !$form.approved) || ($form.status eq '0' && $form.active eq '1' && $form.approved eq '0')}
	{assign var=show_reserve_qty value=1}
{/if}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width="100%" class="report_table table mb-0 text-md-nowrap  table-hover input_no_border small body"   id="docs_items">

				<!--START HEADER-->
				
				<thead class="bg-gray-100">
				<tr >
					<th rowspan=2 width=20 {if $config.sales_order_show_photo}colspan=2{/if}>#</th>
					<th nowrap rowspan=2 width=100>ARMS Code</th>
					<th nowrap rowspan=2 width=80>Article No</th>
					<th nowrap rowspan=2 width=80>MCode</th>
					<th nowrap rowspan=2>SKU Description</th>
					{if $show_reserve_qty}
					<th rowspan=2>Reserve Qty [<a href="javascript:void(alert('Approved Sales Order Quantity from other Sales Order which not yet Delivered and Exported to POS.'))">?</a>]</th>
					{/if}
					<th rowspan="2" width=60>Stock Balance</th>
					{if $config.show_parent_stock_balance}
					  <th rowspan="2" width=60>Parent Stock Balance</th>
					{/if}
					{if $config.sales_order_show_rsp}
						<th rowspan=2 width=60>RSP</th>
						<th rowspan=2 width=60>RSP Discount</th>
					{/if}
					{if $sessioninfo.privilege.SHOW_COST}
					  <th rowspan=2 width=60>Cost<br>({$config.arms_currency.symbol})</th>
					{/if}
					<th rowspan=2 width=60>Selling Price<br>({$config.arms_currency.symbol})</th>
					<th rowspan=2 width=80>UOM</th>
					<th nowrap colspan="2">Qty</th>
					<th rowspan=2 width=60>Total<br>Qty</th>
					<th rowspan="2" width="60">Discount <b>[<a href="javascript:void(show_discount_help());">?</a>]</b></th>
				
					{if $form.is_under_gst}
						<th rowspan="2">Gross<br />Amount</th>
						<th rowspan="2">GST Code</th>
						<th rowspan="2">GST Amt</th>
					{/if}
				 
					<th rowspan=2 width=60>Total Amount {if $form.is_under_gst}Included GST{/if}<BR>({$config.arms_currency.symbol})</th>
					{if $form.delivered}
						<th rowspan=2 width=50>Delivered<BR>Qty</th>
					{/if}
					{if $form.approved}
						<th rowspan="2" width="50">PO</th>
					{/if}
				</tr>
				
				<tr bgcolor=#ffffff>
					<th nowrap width="60"><span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span></th>
					<th nowrap width="60"><span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span></th>
				</tr>
				</thead>
					{assign var=total_amt value=0}
					{assign var=total_qty value=0}
					{assign var=total_ctn value=0}
					{assign var=total_pcs value=0}
				
					{foreach from=$items item=item name=f}
						{include file='sales_order.open.sheet.item_row.tpl' total_delivered_qty=$total_delivered_qty}
					{/foreach}
				<tfoot id="tbl_footer">
					{assign var=show_sub_total value=0}
					{if $form.sheet_discount_amount}
						{assign var=show_sub_total value=1}
					{/if}
				
					<!-- Sub Total -->
					<tr bgcolor="#ffffff" class="normal" height="24" id="tr_sub_total" style="{if !$show_sub_total}display:none;{/if}height:24px;">
						{assign var=colspan value=10}
						{if $sessioninfo.privilege.SHOW_COST}{assign var=colspan value=$colspan+1}{/if}
						{if $config.show_parent_stock_balance}{assign var=colspan value=$colspan+1}{/if}
						{if $config.sales_order_show_photo}{assign var=colspan value=$colspan+1}{/if}
						{if $show_reserve_qty}{assign var=colspan value=$colspan+1}{/if}
						{if $config.sales_order_show_rsp}{assign var=colspan value=$colspan+2}{/if}
						<td colspan="{$colspan}" nowrap class="r"><b>Sub Total</b></td>
						{assign var=sub_total_amt value=$form.total_amount+$form.sheet_discount_amount}
				
						{if $form.is_under_gst}
						  {assign var="total_gross_amount" value=$sub_total_amt-$form.total_gst_amt-$form.sheet_gst_discount}
						  <th class="r" id="td_sub_total_gross_amount" colspan="3">{$total_gross_amount|number_format:2:".":""}</th>
						  <th></th>
						  <th class="r" id="td_sub_total_gst_amount">{$form.total_gst_amt+$form.sheet_gst_discount|number_format:2:".":""}</th>
						{/if}
						<th class="r" id="td_sub_total_amount" colspan="{if $form.is_under_gst}1{else}3{/if}">{$sub_total_amt|number_format:2:".":""}</th>
						{if $form.delivered}<th class="r">-</th>{/if}
						{if $form.approved}<th class="r">-</th>{/if}
					</tr>
					
					<!-- Sheet Discount -->
					<tr bgcolor="#ffffff" class="normal" id="tr_sheet_discount" style="{if !$show_sub_total}display:none;{/if}height:24px;">
						{assign var=colspan value=10}
						{if $sessioninfo.privilege.SHOW_COST}{assign var=colspan value=$colspan+1}{/if}
						{if $config.show_parent_stock_balance}{assign var=colspan value=$colspan+1}{/if}
						{if $config.sales_order_show_photo}{assign var=colspan value=$colspan+1}{/if}
						{if $show_reserve_qty}{assign var=colspan value=$colspan+1}{/if}
						{if $config.sales_order_show_rsp}{assign var=colspan value=$colspan+2}{/if}
						<td colspan="{$colspan}" nowrap align="right" id="td_sheet_discount"><b>Discount (<span id="span_sheet_discount">{$form.sheet_discount|ifzero:''}</span>)</b></td>
						{if $form.is_under_gst}
						  {math assign="sheet_discount_gross_amount" equation="(x-y)*-1" x=$form.sheet_discount_amount|default:0 y=$form.sheet_gst_discount|default:0}
						  <th class="r" id="td_sheet_discount_gross_amount" colspan="3">{$sheet_discount_gross_amount|number_format:2:".":""}</th>
						  <th></th>
						  <th class="r" id="td_sheet_discount_gst_amount">{$form.sheet_gst_discount*-1|number_format:2:".":""}</th>
						{/if}
						<th class="r" id="td_sheet_discount_amount" colspan="{if $form.is_under_gst}1{else}3{/if}">{$form.sheet_discount_amount*-1|default:0|number_format:2:".":""}</th>
						{if $form.delivered}<th class="r">-</th>{/if}
						{if $form.approved}<th class="r">-</th>{/if}
					</tr>
				
					<!-- Total -->
					<tr bgcolor="#ffffff" class="normal" height="24">
						{assign var=colspan value=10}
						{if $sessioninfo.privilege.SHOW_COST}{assign var=colspan value=$colspan+1}{/if}
						{if $config.show_parent_stock_balance}{assign var=colspan value=$colspan+1}{/if}
						{if $config.sales_order_show_photo}{assign var=colspan value=$colspan+1}{/if}
						{if $show_reserve_qty}{assign var=colspan value=$colspan+1}{/if}
						{if $config.sales_order_show_rsp}{assign var=colspan value=$colspan+2}{/if}
						<td colspan="{$colspan}" nowrap align="right"><b>Total</b></td>
						<td width="80">
							<b>
							T.Ctn : <span id="span_total_ctn">{$form.total_ctn|default:$total_ctn|qty_nf}</span><br>
							T.Pcs : <span id="span_total_pcs">{$form.total_pcs|default:$total_pcs|qty_nf}</span>
							</b>
						</td>
						{if $form.is_under_gst}
						  <th class="r" id="th_total_gross_amt" colspan="2">{$form.total_gross_amt|number_format:2:".":""}</th>
						  <th></th>
						  <th class="r" id="td_total_gst_amount">{$form.total_gst_amt|number_format:2:".":""}</th>
						{/if}
						<th class="r" id="th_total_amt" colspan="{if $form.is_under_gst}1{else}2{/if}">
							{$form.total_amount|default:0|number_format:2:".":""}
						</th>
						{if $form.delivered}
							<th class="r" id="th_total_delivered_qty">{$total_delivered_qty|qty_nf}</th>
							<script>count_delivered_qty();</script>
						{/if}
						{if $form.approved}<th class="r">-</th>{/if}
					</tr>
					
				</tfoot>
				</table>
		</div>
	</div>
</div>
