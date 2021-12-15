{*
REVISION HISTORY
================
1/4/2008 3:21:14 PM gary
- add price indicate.

4/4/2008 5:12:36 PM gary
- purchase uom change to uom.

18/4/2008 yinsee
- match highlight_item_id with sku_item_id

7/16/2009 10:35:34 AM Andy
- Add Stock Balance Column

11/5/2009 4:50:47 PM Andy
- add invoice discount. per sheet and per item

6/17/2010 3:10:10 PM Justin
- Do not hightlight the row from Item list when it is Open Item.

6/10/2011 10:54:11 AM Justin
- Added new feature for consignment customer of different currency fields.

6/20/2011 5:34:02 PM Alex
- add latest cost and price indicator column

8/5/2011 4:20:31 PM Andy
- Change DO Invoice Discount format.

10/24/2011 5:45:34 PM Justin
- Added rounding amount when found config "do_enable_cash_sales_rounding" and it's under cash sales module.

12/6/2011 12:10:43 PM Justin
- Added SA column.
- Added to +1 colspan for total amount row when found sa config is on.

2/8/2012 4:48:43 PM Justin
- Added new column "Master UOM" and renamed the current UOM become "DO UOM".
- Modified colspan for total amount row for Master UOM.

4/30/2012 5:34:59 PM Alex
- hide external branch column while in Cash Sales 

7/30/2012 10:37 AM Andy
- Change column header from "Latest Cost" to "Cost" since the cost change to get by DO Date.

3/20/2014 1:30 PM Justin
- Enhanced to to show checklist qty & variance.

5/22/2014 11:53 AM Justin
- Enhanced to colspan + 1 while import from PO.

3/23/2015 10:41 AM Andy
- Fix column error when got scanned qty.

5/25/2015 4:45 PM Andy
- Change the discount label to always same as discount format.
- Remove the foreign sheet discount label.

6/4/2015 11:17 AM Andy
- Fix foreign calculation.

7/29/2015 16:23 PM Joo Chia
- Assign id and class for deliver branch list to enable branch qty from to load the list.

04/07/2016 14:00 Edwin
- Enhanced on show parent stock balance when config.show_parent_stock_balance is enabled.

6/23/2016 2:36 PM Andy
- Enhanced to able to highlight DO row by sku_id.

4/19/2017 2:02 PM Khausalya
- Enhanced changes from RM to use config setting.

6/8/2017 10:01 AM Justin
- Bug fixed on open item will cause the sub total colspan become wrong.

12/6/2018 5:37 PM Justin
- Enhanced to show Old Code column base on config.

12/13/2018 2:48 PM Justin
- Enhanced to always show Art No and MCode in 2 columns instead of either one.

3/11/2019 12:38 PM Andy
- Enhanced to have "Invoice Amount Adjust".

7/3/2019 2:52 PM William
- Added new feature "Disable Auto Parent & Child Distribution".

8/28/2019 9:55 AM William
- Enhanced to added new column for new config "do_custom_column".

11/27/2019 3:07 PM William
- Enhance to show sku item photo when config "do_show_photo" is active.

06/23/2020 04:46 Sheila
- Fixed table boxes alignment and width.

03/09/2021 09:43 AM Ian
- Enhanced to add column rsp price & rsp discount when config "do_show_rsp" is active.
- Adjusted the alignment of the table footer when config "do_show_rsp" is active.
*}

{config_load file="site.conf"}

{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer' and $form.exchange_rate>0 and $form.exchange_rate<>1}
	{assign var=is_currency_mode value=1}
{/if}

{if $form.deliver_branch && !$form.do_branch_id}
<!-- do item table -->
<div class="table-responsive">
	<table width=100%  class="input_no_border small body table mb-0 text-md-nowrap  table-hover" >

		<!--START HEADER-->
		<thead class="bg-gray-100">
		<tr>
			<th rowspan=2 {if $config.do_show_photo}colspan=2{/if} width=20>#</th>
			<th nowrap rowspan=2 width=100>ARMS Code</th>
			<th nowrap rowspan=2 width=80>Article No</th>
			<th nowrap rowspan=2 width=80>MCode</th>
			{if $config.link_code_name && $config.docs_show_link_code}
				<th nowrap rowspan=2 width=80>{$config.link_code_name}</th>
			{/if}
			<th nowrap rowspan=2>SKU Description</th>
		
			{if $config.do_show_rsp}
				<th nowrap rowspan=2>RSP</th>
				<th nowrap rowspan=2>RSP Discount</th>
			{/if}
			{if $form.create_type==3}
			<th rowspan=2  width=60>PO Cost<br />({$config.arms_currency.symbol})</th>
			{/if}
			<th rowspan="2"  width=60>Stock Balance<br />
			(<span id="span_branch_code1"></span>)
			{if $config.show_parent_stock_balance}
				<th rowspan="2"  width=60>Parent Stock Balance<br />
				(<span id="span_parent_branch_code1"></span>)
			{/if}
			<script>change_branch_code_for_stock_balance1();</script>
			</th>
			{if $sessioninfo.privilege.SHOW_COST}
				<th rowspan=2 width=60>Cost</th>
			{/if}
			{if $do_type eq 'credit_sales' && $sessioninfo.privilege.SHOW_COST}
				<th rowspan=2 width=60>Price<br />Indicate</th>
			{/if}
			<th rowspan=2  width=60>Price<br />({$config.arms_currency.symbol})</th>
			<th rowspan=2 width=80>Master<br />UOM</th>
			<th rowspan=2 width=80>DO UOM</th>
			<th nowrap colspan={count multi=1 var=$form.deliver_branch}>Qty</th>
			<th rowspan=2 width=60>Total<br />Qty</th>
			
			{if $form.is_under_gst}
				<th rowspan="2">Gross<br />Amount</th>
				<th rowspan="2">GST Code</th>
			{/if}
			
			<th rowspan=2 width=60>Total Amount {if $form.is_under_gst}Included GST{/if}<br />({$config.arms_currency.symbol})</th>
			
			{if $show_discount}
				<th rowspan="2" width="60">Invoice<br />Discount
					<b>[<a href="javascript:void(show_discount_help());">?</a>]</b>
				</th>
				
				{if $form.is_under_gst}
					<th rowspan="2">Gross Invoice Amount</th>
					<th rowspan="2">Invoice GST</th>
				{/if}
				
				<th rowspan="2" width="60">Invoice<br />Amount {if $form.is_under_gst}Included GST<br/>{/if}({$config.arms_currency.symbol})</th>
			{/if}
			{if $config.do_custom_column}
				{foreach from=$config.do_custom_column item=col}
					<th rowspan="2">{$col.desc}</th>
				{/foreach}
			{/if}
		</tr>
		<tr bgcolor=#ffffff>
		
		{if $form.deliver_branch}
		{section name=i loop=$branch}
		{if in_array($branch[i].id,$form.deliver_branch)}
			<th nowrap id="{$branch[i].id}" class="deliver_branch_list">{$branch[i].code}<br />
		
			<table width="100%">
				<tr>
					<td align="center">
					<input class="tbl_col_ctn" style="background:#fff;border:1px solid #ccc;width: 40px;text-align:center;font-size: 11px;font-weight: bold;padding-right: 2px;cursor: context-menu;" value="Ctn" disabled="">
					</td>
					<td align="center">
					<input class="tbl_col_ctn" style="background:#fc9;border:1px solid #ccc;width: 40px;text-align:center;font-size: 11px;font-weight: bold;padding-right: 2px;cursor: context-menu;" class="tbl_col_pcs" value="Pcs" disabled="">
					</td>
				</tr>
			</table>
		
			</th>
		{/if}
		{/section}
		{/if}
		</tr>
		</thead>
		<!--END TABLE HEADER -->
		
		
		<!--START TABLE ITEMS-->
		<tbody class="fs-08" id="do_items">
		{assign var=total_ctn value=0}
		{assign var=total_pcs value=0}
		
		{foreach from=$do_items item=item name=fitem}
			{section name=i loop=$branch}
			{assign var=bid value=`$branch[i].id`}
			{assign var=total_ctn value=$total_ctn+$item.ctn.$bid}
			{assign var=total_pcs value=$total_pcs+$item.pcs.$bid}
			{/section}
		<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="titem{$item.id}" {if ($smarty.request.highlight_item_id eq $item.sku_item_id or $smarty.request.highlight_sku_id eq $item.sku_id) && !$item.oi}class=highlight_row{/if}>
		{include file=do.new.do_row.tpl}
		</tr>
		{/foreach}
		</tbody>
		{if !$form.status}
		<script>get_label_indicator();</script>
		{/if}
		
		<!--END TABLE ITEMS-->
		
		
		<!-- START TABLE FOOTER-->
		<tfoot>
		{assign var=default_colspan value=10}
		{if $form.create_type==3}{assign var=default_colspan value=$default_colspan+1}{/if}
		{if !$sessioninfo.privilege.SHOW_COST}{assign var=default_colspan value=$default_colspan-1}{/if}
		{if $config.show_parent_stock_balance}{assign var=default_colspan value=$default_colspan+1}{/if}
		{if $config.link_code_name && $config.docs_show_link_code}{assign var=default_colspan value=$default_colspan+1}{/if}
		{if $config.do_show_photo}{assign var=default_colspan value=$default_colspan+1}{/if}
		{if $config.do_show_rsp}{assign var=default_colspan value=$default_colspan+2}{/if}
		{count var=$form.deliver_branch multi=1 offset=$default_colspan assign="colspan"}
		
		{* Sub Total *}
		<tr style="background-color:#ffffff;">
			<td colspan="{$colspan}" align="right">
				<input type="hidden" name="colspan_length" value="{$colspan}">
				<span><b>Sub Total</b></span>
			</td>
			
			<td>&nbsp;</td>
			
			{if $form.is_under_gst}
				<td align="right"><b><span id="span-sub_total_gross_amt">{$form.sub_total_gross_amt|number_format:2}</span></b></td>
				<td align="right"><b><span id="span-sub_total_gst_amt">{$form.sub_total_gst_amt|number_format:2}</span></b></td>
			{/if}
			
			<td align="right"><b><span id="span-sub_total_amt">{$form.sub_total_amt|number_format:2}</span></b></td>
			
			{if $show_discount}
				{if $form.is_under_gst}
					<td>&nbsp;</td> {* Discount format *}
					<td align="right"><b><span id="span-sub_total_gross_inv_amt">{$form.inv_sub_total_gross_amt|number_format:2}</span></b></td>
					<td align="right"><b><span id="span-sub_total_inv_gst_amt">{$form.inv_sub_total_gst_amt|number_format:2}</span></b></td>
				{else}
					<td>&nbsp;</td>
				{/if}
				
				<td align="right"><b><span id="span-sub_total_inv_amt">{$form.sub_total_inv_amt|number_format:2}</span></b></td>
			{/if}
			{if $config.do_custom_column}
				{foreach from=$config.do_custom_column item=col}
					<td></td>
				{/foreach}
			{/if}
		</tr>
		
		{* Sheet Invoice Discount *}
		<tr style="background-color:#ffffff;{if !$form.discount || !$show_discount}display:none;{/if}" id="tr_sheet_inv_discount_row">
			<td colspan="{$colspan}" align="right">
				<span><b>Invoice Discount</b></span>
			</td>
			
			<td>
				<div class="small" style="color:blue;white-space: nowrap;" id="div_sheet_discount">
					{if $form.discount}
						{* if $form.price_indicate ne 1 and $is_currency_mode}
							{assign var=temp value=$form.sub_total_inv_amt-$form.total_inv_amt}
							({$temp|number_format:2})
						{else}
							({$form.discount})
						{/if *}
						({$form.discount})
					{/if}
				</div>
			</td>
			
			{if $form.is_under_gst}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
			
			<td>&nbsp;</td>
			
			{if $show_discount}
				{if $form.is_under_gst}
					<td>&nbsp;</td> {* Discount format *}
					<td align="right"><b><span id="span-inv_gross_discount_amt">{$form.inv_sheet_gross_discount_amt|number_format:2}</span></b></td>
					<td align="right"><b><span id="span-inv_gst_discount_amt">{$form.inv_sheet_gst_discount|number_format:2}</span></b></td>
				{else}
					<td>&nbsp;</td>
				{/if}
				
				<td align="right"><b><span id="span-inv_discount_amt">{$form.inv_sheet_discount_amt|number_format:2}</span></b></td>
			{/if}
			{if $config.do_custom_column}
				{foreach from=$config.do_custom_column item=col}
					<td></td>
				{/foreach}
			{/if}
		</tr>
		
		<!-- total -->
		<tr bgcolor=#ffffff class=normal height=24 id="total">
		
		<td colspan="{$colspan}" nowrap align=right>
		<b>TOTAL</b>
		</td>
		
		<td width=80><b>
		T.Ctn : <span id=t_ctn>{$form.total_ctn|default:$total_ctn}</span><br />
		T.Pcs : <span id=t_pcs>{$form.total_pcs|default:$total_pcs}</span>
		</b></td>
		
			{if $form.is_under_gst}
				<td align="right"><b><span id="span-do_total_gross_amt">{$form.do_total_gross_amt|number_format:2}</span></b></td>
				<td align="right"><b><span id="span-do_total_gst_amt">{$form.do_total_gst_amt|number_format:2}</span></b></td>
			{/if}
			
		<th align=right id=display_total_amount class="uom">
		{$form.total_amount|default:0|number_format:2:".":""}
		</th>
		
			{if $show_discount}
				{if $form.is_under_gst}
					<td>&nbsp;</td> {* Discount format *}
					<td align="right"><b><span id="span-inv_total_gross_amt">{$form.inv_total_gross_amt|number_format:2}</span></b></td>
					<td align="right"><b><span id="span-inv_total_gst_amt">{$form.inv_total_gst_amt|number_format:2}</span></b></td>
				{else}
					<td>&nbsp;</td>
				{/if}
				
				<th class="r">
					<span id="span_total_inv_amt">{$form.total_inv_amt|default:'0'|number_format:2}</span><input type="hidden" name="total_inv_amt" id="inp_total_inv_amt" value="{$form.total_inv_amt}" />
				</th>
				
				<!-- Foreign -->
				{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
					<th class="r total_foreign_inv_amt">
						<div class="small" style="color:blue;white-space: nowrap;" id="div_sheet_foreign_discount">
							{* if $form.discount}
								Discount
								{if $form.price_indicate eq 1 and $is_currency_mode}
									{assign var=temp value=$form.sub_total_foreign_inv_amt-$form.total_foreign_inv_amt}
									({$temp|number_format:2})
								{else}
									({$form.discount})
								{/if}
							{/if *}
						</div>
						<span id="span_total_foreign_inv_amt">{$form.total_foreign_inv_amt|default:'0'|number_format:2}</span>
						<input type="hidden" name="total_foreign_inv_amt" id="inp_total_foreign_inv_amt" value="{$form.total_foreign_inv_amt}" /></th>
					</th>
				{/if}
			{/if}
			{if $config.do_custom_column}
				{foreach from=$config.do_custom_column item=col}
					<td></td>
				{/foreach}
			{/if}
		<input type=hidden id=total_ctn name=total_ctn value="{$form.total_ctn|default:$total_ctn}">
		<input type=hidden id=total_pcs name=total_pcs value="{$form.total_pcs|default:$total_pcs}">
		<input type=hidden id=total_qty name=total_qty value="{$form.total_qty}">
		<input type=hidden id=total_rcv name=total_rcv value="{$form.total_rcv}">
		
		<input id="total_amount" name="total_amount" type=hidden value="{$form.total_amount|number_format:2:".":""}">
		
		</tr>
		
		</tfoot>
		<!-- END TABLE FOOTER-->
		</table>
</div>

<!--######################################################################################-->
<!--######################################################################################-->
{else}

<!-- do item table -->
{if $checklist_disable_parent_child eq 1}<p>* Disable Auto Parent & Child Distribution</p>{/if}
<div class="table-responsive">
	<table width=100%  class="input_no_border body table mb-0 text-md-nowrap  table-hover" >

		<!--START HEADER-->
		<thead class="small bg-gray-100">
		<tr>
			<th rowspan=2 {if $config.do_show_photo}colspan=2{/if} width=20>#</th>
			<th nowrap rowspan=2 width=100>ARMS Code</th>
			<th nowrap rowspan=2 width=80>Article No</th>
			<th nowrap rowspan=2 width=80>MCode</th>
			{if $config.link_code_name && $config.docs_show_link_code}
				<th nowrap rowspan=2 width=80>{$config.link_code_name}</th>
			{/if}
			<th nowrap rowspan=2>SKU Description</th>
		
			{if $config.do_show_rsp}
				<th nowrap rowspan=2>RSP</th>
				<th nowrap rowspan=2>RSP Discount</th>
			{/if}
			{if $form.create_type==3}
			<th rowspan=2  width=60>PO Cost<br />(<span id="span_poc_currency_code">{$config.arms_currency.symbol}</span>)</th>
			{/if}
			{assign var=colspan value=2}
			
			{if $do_type eq 'credit_sales' || $do_type eq 'open'}{assign var=colspan value=$colspan-1}{/if}
			<th colspan="{$colspan}"  width=60>Stock Balance</th>
			{if $config.show_parent_stock_balance}
				<th colspan="{$colspan}"  width=60>Parent Stock Balance</th>
			{/if}
			{if $config.masterfile_enable_sa && $do_type ne 'transfer'}
				<th rowspan="2" width="60">Sales<br />Agent</th>
			{/if}
			{if $sessioninfo.privilege.SHOW_COST}
				<th rowspan=2 width=60>Cost</th>
			{/if}
			{if $do_type eq 'credit_sales' && $sessioninfo.privilege.SHOW_COST}
				<th rowspan=2 width=60>Price<br />Indicator</th>
			{/if}
			<th rowspan=2 width=60>Price<br />({$config.arms_currency.symbol})</th>
			{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
				<th rowspan=2 width=60 id="foreign_price">Price<br />(<span id="span_p_currency_code"></span>)</th>
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
			
			{if $config.do_custom_column}
				{foreach from=$config.do_custom_column item=col}
					<th rowspan="2">{$col.desc}</th>
				{/foreach}
			{/if}
			{if $have_scan_items}
				<th rowspan=2 width=60>Scanned<br />Qty</th>
				<th rowspan=2 width=60>Variance</th>
			{/if}
		</tr>
		<tr bgcolor=#ffffff>
			<th>(<span id="span_branch_code1"></span>)</th>
			
			{if $do_type ne 'credit_sales' && $do_type ne 'open'}
			<th>(<span id="span_branch_code2"></span>)</th>
			{/if}
			
			{if $config.show_parent_stock_balance}
				<th>(<span id="span_parent_branch_code1"></span>)</th>
			
				{if $do_type eq 'transfer'}
					<th>(<span id="span_parent_branch_code2"></span>)</th>
				{/if}
			{/if}
			<script>change_branch_code_for_stock_balance1();</script>
			{if $do_type eq 'transfer'}
					<script>change_branch_code_for_stock_balance2();</script>
			{/if}
		<th align=center>
		
			<table width="100%">
				<tr>
					<td align="center">
					<input class="tbl_col_ctn" style="background:#fff;border:1px solid #ccc;width: 40px;text-align:center;font-size: 11px;font-weight: bold;padding-right: 2px;cursor: context-menu;" value="Ctn" disabled="">
					</td>
					<td align="center">
					<input class="tbl_col_ctn" style="background:#fc9;border:1px solid #ccc;width: 40px;text-align:center;font-size: 11px;font-weight: bold;padding-right: 2px;cursor: context-menu;" class="tbl_col_pcs" value="Pcs" disabled="">
					</td>
				</tr>
			</table>
		</th>
		</tr>
		</thead>
		<!--END TABLE HEADER -->
		
		
		<!--START TABLE ITEMS-->
		<tbody class="fs-08" id="do_items">
		{assign var=total_ctn value=0}
		{assign var=total_pcs value=0}
		
		{foreach from=$do_items item=item name=fitem}
			{assign var=total_ctn value=$total_ctn+$item.ctn}
			{assign var=total_pcs value=$total_pcs+$item.pcs}
			{if $have_scan_items}
				{assign var=total_scan_qty value=$total_scan_qty+$item.scan_qty}
				{assign var=total_variance value=$total_variance+$item.variance}
			{/if}
			<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="titem{$item.id}" {if ($smarty.request.highlight_item_id eq $item.sku_item_id or $smarty.request.highlight_sku_id eq $item.sku_id) && !$item.oi}class=highlight_row{/if}>
			{include file='do.new.do_row.single_branch.tpl'}
			</tr>
		{/foreach}
		</tbody>
		{if !$form.status}
		<script>get_label_indicator();</script>
		{/if}
		<!--END TABLE ITEMS-->
		
		
		<!-- START TABLE FOOTER-->
		<tfoot class="bg-gray-100">
		<!-- total -->
		{if $form.create_type==3}
		{assign var=colspan value=9}
		{else}
		{assign var=colspan value=8}
		{/if}
		{if !$form.open_info.name}
		{assign var=colspan value=$colspan+1}
		{/if}
		{if $config.show_parent_stock_balance}
			{if $do_type eq 'transfer'}
				{assign var=colspan value=$colspan+2}
			{else}
				{assign var=colspan value=$colspan+1}
			{/if}
		{/if}
		{if $form.deliver_branch && !$form.do_branch_id}
			{assign var=colspan value=$colspan}
		{else}
			{assign var=colspan value=$colspan+2}
		{/if}
		
		{if $do_type eq 'open'}
			{assign var=colspan value=$colspan-1}
		{/if}
		
		{if !$sessioninfo.privilege.SHOW_COST}
			{if $do_type eq 'credit_sales'}
				{assign var=colspan value=$colspan-2}
			{else}
				{assign var=colspan value=$colspan-1}
			{/if}
		{/if}
		{if $config.masterfile_enable_sa && $do_type ne 'transfer'}
			{assign var=colspan value=$colspan+1}
		{/if}
		{if $config.do_show_photo}{assign var=colspan value=$colspan+1}{/if}
		{* if $form.is_under_gst}
			{assign var=colspan value=$colspan+2}
		{/if *}
		{if $config.do_show_rsp}{assign var=colspan value=$colspan+2}{/if}
		{assign var=colspan value=$colspan+2}
		{if $config.link_code_name && $config.docs_show_link_code}{assign var=colspan value=$colspan+1}{/if}
		
		{* Sub Total *}
		<tr style="background-color:#ffffff;" id="tr_sub_total">
			<td colspan="{$colspan}" align="right" id="td_sub_total">
				<input type="hidden" name="colspan_length" value="{$colspan}">
				<span><b>Sub Total</b></span>
			</td>
			
			<td>&nbsp;</td>
			
			{if $form.is_under_gst}
				<td align="right"><b><span id="span-sub_total_gross_amt">{$form.sub_total_gross_amt|number_format:2}</span></b></td>
				<td align="right"><b><span id="span-sub_total_gst_amt">{$form.sub_total_gst_amt|number_format:2}</span></b></td>
			{/if}
			
			<td align="right"><b><span id="span-sub_total_amt">{$form.sub_total_amt|number_format:2}</span></b></td>
			{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
				<td class="td_sub_total_foreign_col"><span id="span_sub_total_foreign_amt"></span></td>
			{/if}
			
			{if $show_discount}
				{if $form.is_under_gst}
					<td>&nbsp;</td> {* Discount format *}
					<td align="right"><b><span id="span-sub_total_gross_inv_amt">{$form.inv_sub_total_gross_amt|number_format:2}</span></b></td>
					<td align="right"><b><span id="span-sub_total_inv_gst_amt">{$form.inv_sub_total_gst_amt|number_format:2}</span></b></td>
				{else}
					<td>&nbsp;</td>
				{/if}
				
				<td align="right"><b><span id="span-sub_total_inv_amt">{$form.sub_total_inv_amt|number_format:2}</span></b></td>
				
				{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
					<th class="td_sub_total_foreign_col" align="right"><span id="span-sub_total_foreign_inv_amt">{$form.sub_total_foreign_inv_amt|number_format:2}</span></th>
				{/if}
			{/if}
			
			{if $config.do_custom_column}
				{foreach from=$config.do_custom_column item=col}
					<td></td>
				{/foreach}
			{/if}
			{if $have_scan_items}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
		</tr>
		
		{* Sheet Invoice Discount *}
		<tr style="background-color:#ffffff;{if !$form.discount}display:none;{/if}" id="tr_sheet_inv_discount_row">
			<td colspan="{$colspan}" align="right" id="td_inv_discount">
				<span><b>Invoice Discount</b></span>
			</td>
			
			<td>
				<div class="small" style="color:blue;white-space: nowrap;" id="div_sheet_discount">
					{if $form.discount}
						{* if $form.price_indicate ne 1 and $is_currency_mode}
							{assign var=temp value=$form.sub_total_inv_amt-$form.total_inv_amt}
							({$temp|number_format:2})
						{else}
							({$form.discount})
						{/if *}
						({$form.discount})
					{/if}
				</div>
			</td>
			
			{if $form.is_under_gst}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
			
			<td>&nbsp;</td>
			{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
				<td class="td_inv_discount_foreign_col">&nbsp;</td>
			{/if}
			
			{if $show_discount}
				{if $form.is_under_gst}
					<td>&nbsp;</td> {* Discount format *}
					<td align="right"><b><span id="span-inv_gross_discount_amt">{$form.inv_sheet_gross_discount_amt|number_format:2}</span></b></td>
					<td align="right"><b><span id="span-inv_gst_discount_amt">{$form.inv_sheet_gst_discount|number_format:2}</span></b></td>
				{else}
					<td>&nbsp;</td>
				{/if}
				
				<td align="right"><b><span id="span-inv_discount_amt">{$form.inv_sheet_discount_amt|number_format:2}</span></b></td>
				
				{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
					<td class="td_inv_discount_foreign_col" align="right"><span id="span-foreign_inv_discount_amt">{$form.inv_sheet_foreign_discount_amt|number_format:2}</span></td>
				{/if}
			{/if}
			
			{if $config.do_custom_column}
				{foreach from=$config.do_custom_column item=col}
					<td></td>
				{/foreach}
			{/if}
			{if $have_scan_items}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
		</tr>
		
		{* Invoice Amount Adjust *}
		{* if $show_amt_adj *}
		{if ($readonly && $form.inv_sheet_adj_amt) || (!$readonly && $config.do_have_amt_adj and ($form.do_type eq 'open' or $form.do_type eq 'credit_sales'))}
			<tr style="background-color:#ffffff;" id="tr_sheet_inv_adj_row">
				<td colspan="{$colspan}" align="right">
					<span><b>Invoice Amount Adjust</b></span>
				</td>
				<td>&nbsp;</td>
				{if $form.is_under_gst}
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				{/if}
				
				<td>&nbsp;</td>
				
				{if $show_discount}
					{if $form.is_under_gst}
						<td>&nbsp;</td> {* Discount format *}
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					{else}
						<td>&nbsp;</td>
					{/if}
					
					<td align="right">
						<input type="text" name="inv_sheet_adj_amt" value="{$form.inv_sheet_adj_amt|number_format:2:".":""}" style="text-align:right;width:50px;" onChange="DO_MODULE.invoice_adj_change();" {if $readonly}disabled {/if} />
					</td>
					
				{/if}
				
				{if $config.do_custom_column}
					{foreach from=$config.do_custom_column item=col}
						<td></td>
					{/foreach}
				{/if}
				{if $have_scan_items}
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				{/if}
			</tr>
		{/if}
		
		{if $config.do_enable_cash_sales_rounding && $do_type eq 'open'}
			<tr class="normal" height="24" id="total" style="background-color:#ffffff;">
				<input type="hidden" id="total_round_inv_amt" name="total_round_inv_amt" value="{$form.total_round_inv_amt}">
				<td colspan="{$colspan}" nowrap align="right" id="total_colspan"><b>Total Before Round</b></td>
				
				<td>&nbsp;</td>
				
				{if $form.is_under_gst}
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				{/if}
				
				
				<td class="r">
					<b><span id="span_ttl_bf_round_amt">{$form.total_amount-$form.total_round_amt|number_format:2:".":""}</span></b>
				</td>
				
				{if $show_discount}
					{if $form.is_under_gst}
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					{/if}
					<td>&nbsp;</td>
					<td class="r">
						<b><span id="span_ttl_bf_round_inv_amt">{$form.total_inv_amt-$form.total_round_inv_amt|number_format:2:".":""}</span></b>
					</td>
				{/if}
				
				{if $config.do_custom_column}
					{foreach from=$config.do_custom_column item=col}
						<td></td>
					{/foreach}
				{/if}
				{if $have_scan_items}
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				{/if}
			</tr>
			
			<tr class="normal" height="24" id="total" style="background-color:#ffffff;">
				<input type="hidden" id="total_round_amt" name="total_round_amt" value="{$form.total_round_amt}">
				<td colspan="{$colspan}" nowrap align="right" id="total_colspan"><b>Rounding</b></td>
				<td>&nbsp;</td>
				
				{if $form.is_under_gst}
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				{/if}
				
				<td class="r">
					<b><span id="span_rounding_amt">{$form.total_round_amt|number_format:2:".":""}</span></b>
				</td>
		
				{if $show_discount}
					{if $form.is_under_gst}
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					{/if}
					<td>&nbsp;</td>
					<td class="r">
						<b><span id="span_rounding_inv_amt">{$form.total_round_inv_amt|number_format:2:".":""}</span></b>
					</td>
				{/if}
				
				{if $config.do_custom_column}
					{foreach from=$config.do_custom_column item=col}
						<td></td>
					{/foreach}
				{/if}
				{if $have_scan_items}
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				{/if}
			</tr>
		{/if}
		
		<tr bgcolor="#ffffff" class="normal" height="24" id="total">
			<td colspan="{$colspan}" nowrap align=right id="total_colspan"><b>TOTAL {if $config.do_enable_cash_sales_rounding && $do_type eq 'open'}After Round{/if}</b></td>
			{if $do_type eq 'transfer' and $config.do_use_rcv_pcs}
				<td>
					<b>
					T.Qty : <span id="t_qty">{$form.total_qty|default:'0'}</span><br />
					T.Rcv : <span id="t_rcv">{$form.total_rcv|default:'0'}</span>
					</b>
				</td>
			{/if}
				
			<td width=80><b>
			T.Ctn : <span id=t_ctn>{$form.total_ctn|default:$total_ctn}</span><br />
			T.Pcs : <span id=t_pcs>{$form.total_pcs|default:$total_pcs}</span>
			</b></td>
		
				
			{if $form.is_under_gst}
				<td align="right"><b><span id="span-do_total_gross_amt">{$form.do_total_gross_amt|number_format:2}</span></b></td>
				<td align="right"><b><span id="span-do_total_gst_amt">{$form.do_total_gst_amt|number_format:2}</span></b></td>
			{/if}
				
			<th align=right id=display_total_amount class="uom">{$form.total_amount|default:0|number_format:2:".":""}</th>
			
			{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
				
				<th align="right" id="display_total_foreign_amount" class="uom total_foreign_amount">
				{$form.total_foreign_amount|default:0|number_format:2:".":""}
				</th>
				<input id="total_foreign_amount" name="total_foreign_amount" type="hidden" value="{$form.total_foreign_amount|number_format:2:".":""}">
			{/if}
			
			{if $show_discount}
				{if $form.is_under_gst}
					<td>&nbsp;</td> {* Discount format *}
					<td align="right"><b><span id="span-inv_total_gross_amt">{$form.inv_total_gross_amt|number_format:2}</span></b></td>
					<td align="right"><b><span id="span-inv_total_gst_amt">{$form.inv_total_gst_amt|number_format:2}</span></b></td>
				{else}
					<td>&nbsp;</td>
				{/if}
				
				<th class="r">
					<span id="span_total_inv_amt">{$form.total_inv_amt|default:'0'|number_format:2}</span><input type="hidden" name="total_inv_amt" id="inp_total_inv_amt" value="{$form.total_inv_amt}" />
				</th>
				
				<!-- Foreign -->
				{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
					<th class="r total_foreign_inv_amt">
						<div class="small" style="color:blue;white-space: nowrap;" id="div_sheet_foreign_discount">
							{* if $form.discount}
								Discount
								{if $form.price_indicate eq 1 and $is_currency_mode}
									{assign var=temp value=$form.sub_total_foreign_inv_amt-$form.total_foreign_inv_amt}
									({$temp|number_format:2})
								{else}
									({$form.discount})
								{/if}
							{/if *}
						</div>
						<span id="span_total_foreign_inv_amt">{$form.total_foreign_inv_amt|default:'0'|number_format:2}</span>
						<input type="hidden" name="total_foreign_inv_amt" id="inp_total_foreign_inv_amt" value="{$form.total_foreign_inv_amt}" /></th>
					</th>
				{/if}
			{/if}
		
			{if $config.do_custom_column}
				{foreach from=$config.do_custom_column item=col}
					<td></td>
				{/foreach}
			{/if}
			{if $have_scan_items}
				<th class="r">{$total_scan_qty}</th>
				<th class="r">{$total_variance}</th>
			{/if}
		
			<input type=hidden id=total_ctn name=total_ctn value="{$form.total_ctn|default:$total_ctn}">
			<input type=hidden id=total_pcs name=total_pcs value="{$form.total_pcs|default:$total_pcs}">
			<input type=hidden id=total_qty name=total_qty value="{$form.total_qty}">
			<input type=hidden id=total_rcv name=total_rcv value="{$form.total_rcv}">
		
			<input id="total_amount" name="total_amount" type="hidden" value="{$form.total_amount|number_format:2:".":""}">
		</tr>
		</tfoot>
		<!-- END TABLE FOOTER-->
		</table>
</div>
{/if}
