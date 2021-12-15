{*
5/22/2008 3:49:40 PM yinsee
- fix bug when open PO in approval mode, javascript broken (calling of undefined function row_recalc()

6/9/2008 3:17:19 PM yinsee
- fix remark2 bug

5/12/2009 4:15:00 PM Andy
- Hide Cost if no $sessioninfo.privilege.SHOW_COST
- Hide GP if no $sessioninfo.privilege.SHOW_REPORT_GP

7/31/2009 4:19:27 PM Andy
- Edit colspan control

1/12/2011 2:30:18 PM Alex
- add $sessioninfo.privilege.PO_ADD_OTHER_DEPT on (id=all_dept) for searching items from other departments

3/8/2011 2:00:20 PM Andy
- Add checking for highlight_sku_id for the row which need highlight.

5/11/2011 9:55:57 AM Andy
- Increase the width of SKU autocomplete dropdown.

5/11/2011 5:42:24 PM Andy
- Fix row highlighting bugs.

6/7/2011 12:54:49 PM Andy
- Add checking for config "po_use_simple_mode" and only show some simple column.

6/22/2011 11:08:20 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

8/25/2011 4:30:00 PM Andy
- Change PO to ignore "SHOW COST" privilege and will show Cost & Cost indicator all the time.

9/8/2011 4:37:08 PM Alex
- add the missing T.selling/T.cost and total qty and foc when single branch

9/14/2011 12:42:14 PM Alex
- fix total selling and total cost calculation bugs

9/20/2011 12:28:11 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

3/1/2012 11:40:07 AM Alex
- add scan barcode

4/4/2012 2:47:50 PM Andy
- Add show relationship between PO and SO.

8/10/2012 11:12 AM Andy
- Add purchase agreement control.

3/11/2013 4:21 PM Andy
- Fix when select vendor or change department, the page always jumping to focus on search sku, change to only focus when user change search sku type.

07/31/2013 03:47 PM Justin
- Bug fixed on sometime total amt/discount column not match with header

11/8/2014 10:40 AM Justin
- Enhanced to have GST calculation and settings.

5/15/2015 10:52 AM Justin
- Bug fixed on GP for amount include GST is calculated wrongly.

1/11/2017 11:33 AM Andy
- Enhanced to show Nett Selling Price all the time.
- Enhanced to check gst selling price when branch is under gst.

2/21/2017 4:32 PM Andy
- Fixed to auto convert Transportation Charges to double.

4/18/2017 11:37 AM Andy
- Change to use data stored in database instead of recalculate everytime.

5/8/2017 12:58 PM Andy
- Fixed po_amount warning message.

8/21/2017 10:23 AM Andy
- Change cost_help() and discount_help() to use show_markup_help() and show_discount_help().

4/12/2018 4:11 PM Andy
- Added Foreign Currency feature.
- Enhanced to hide PO Amount Include GST GP and GP % if the branch is not under GST.

12/7/2018 2:25 PM Justin
- Enhanced to show Old Code column base on config.

12/13/2018 2:48 PM Justin
- Enhanced to always show Art No and MCode in 2 columns instead of either one.

1/8/2019 2:36 PM Andy
- Fixed the word "Note:" should be hide if no note to be displayed.

05/14/2019 4:40 PM Sheila
- Updated button color 

06/22/2020 11:50 AM Sheila
- Fixed table boxes alignment and width.

8/4/2020 6:08 PM Andy
- Added fixed header and column.
*}
{config_load file="site.conf"}
{*if BRANCH_CODE=='HQ' && $form.deliver_to*}
{if $form.branch_id==1 && !$form.po_branch_id && is_array($form.deliver_to)}
	{assign var=view_hq value=1}
{else}
	{assign var=view_hq value=0}
{/if}

{if $form.is_under_gst}
	{assign var=gst_colspan value=1}
	{assign var=gst_colspan value=$gst_colspan+1}
	{assign var=gst_rowspan value=1}
{else}
	{assign var=gst_colspan value=0}
	{assign var=gst_rowspan value=0}
{/if}

<input type=hidden id="total_check" name="total_check" value="{$total.ctn+$total.qty+$total.foc|qty_nf:'.':''}">

{capture assign=str_note}
	{if $form.is_under_gst}
		* N.S.P (Nett Selling Price) - Selling Price exclude GST (for GST Inclusive Item)<br />
	{/if}
	{if $form.currency_code}
		* Selling Price is always at Base Currency.<br />
		* {$LANG.BASE_CURRENCY_CONVERT_NOTICE}<br />
	{/if}
{/capture}

{if trim($str_note)}
<p><b>
<font color="red">Note:</font><br />
{$str_note}
</b></p>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<table width=100% style="overflow: auto; padding:5px; background-color:#fe9 ;" class="input_no_border small body fixed_header" border=0 cellspacing=1 cellpadding=1 id=tbl_items>
			<thead>
			<!--START MULTIPLE DELIVER BRANCHES-->
			{if $view_hq}
				<tr bgcolor="#ffffff">
					<th colspan="2" rowspan="2">#</th>
					<th nowrap rowspan="2">Article No</th>
					<th nowrap rowspan="2">MCode</th>
					{if $config.link_code_name && $config.docs_show_link_code}
						<th nowrap rowspan="2">{$config.link_code_name}</th>
					{/if}
					<th nowrap rowspan="2" scope="row">SKU Description</th>
					<th rowspan="2">Selling<br>UOM</th>
					<th rowspan="2">Purchase<br>UOM</th>
					{if $form.po_option eq '1'}
						<th colspan="2">Cost Price</th>
					{else}
						<th rowspan="2">Cost<br>Price</th>
						<th rowspan="2">Cost<br>Indicate</th>
					{/if}
					{assign var=col_multi value=2}
					{if $config.po_use_simple_mode}{assign var=col_multi value=1}{/if}
					<th nowrap colspan="{count multi=$col_multi var=$form.deliver_to}">Qty</th>		
					<th rowspan="2" nowrap style="{if $config.po_use_simple_mode}display:none;{/if}">Sales Trend<br>	
						<input class="tbl_col_salestrend" style="border:1px solid #ccc;background:#ccc; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="1M" disabled="">
						<input class="tbl_col_salestrend" style="border:1px solid #ccc;background:#ddd; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="3M" disabled="">
						<input class="tbl_col_salestrend" style="border:1px solid #ccc;background:#ccc; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="6M" disabled="">
						<input class="tbl_col_salestrend" style="border:1px solid #ccc;background:#ddd; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="12M" disabled="">
					</th>
					<th rowspan="2">Total<br>Qty</th>
					<th rowspan="2" style="{if $config.po_use_simple_mode}display:none;{/if}">Total<br>FOC</th>
					<th rowspan="2">Gross<br>Amount</th>
					<th rowspan="2" style="{if $config.po_use_simple_mode}display:none;{/if}">Tax<br>(%)</th>
					<th rowspan="2" style="{if $config.po_use_simple_mode}display:none;{/if}">Discount<br>[<a href="javascript:void(show_discount_help())">?</a>]</th>
					<th rowspan="2" style="{if $config.po_use_simple_mode}display:none;{/if}">Nett<br>Amount</th>
					{if $form.is_under_gst}
						<th rowspan="2">GST<br />Code</th>
						<th rowspan="2">Amount<br />Include<br />GST</th>
					{/if}
					<th rowspan=2>Total<br>Selling</th>
					<th rowspan=2 {if !$sessioninfo.privilege.SHOW_REPORT_GP}style="display:none;"{/if}>Gross<br>Profit</th>
					<th rowspan=2>Profit(%)</th>
					<!-- create by sales order -->
					{if $form.po_create_type eq 1}
						<th>Sales Order</th>
					{/if}
				</tr>
				
				<tr bgcolor="#ffffff">
					{if $form.po_option eq '1'}
						<th>HQ</th>
						<th>Branch</th>
					{/if}
					{section name=i loop=$branch}
					{if in_array($branch[i].id,$form.deliver_to)}
						<th nowrap>{$branch[i].code}<br>
							<table width="100%">
								<tr>
									<td align="center">
									<input class="tbl_col_ctn" style="background:#fff;border:1px solid #ccc;width: 40px;text-align:center;font-size: 10px;font-weight: bold;padding-right: 2px;cursor: context-menu;" value="Ctn" disabled="">
									</td>
									<td align="center">
									<input class="tbl_col_ctn" style="background:#fc9;border:1px solid #ccc;width: 40px;text-align:center;font-size: 10px;font-weight: bold;padding-right: 2px;cursor: context-menu;" class="tbl_col_pcs" value="Pcs" disabled="">
									</td>
								</tr>
							</table>
						</th>
						<!-- FOC -->
						<th nowrap style="{if $config.po_use_simple_mode}display:none;{/if}">FOC<br>
							<table width="100%">
								<tr>
									<td align="center">
									<input class="tbl_col_ctn" style="background:#fff;border:1px solid #ccc;width: 40px;text-align:center;font-size: 10px;font-weight: bold;padding-right: 2px;cursor: context-menu;" value="Ctn" disabled="">
									</td>
									<td align="center">
									<input class="tbl_col_pcs" style="background:#fc9;border:1px solid #ccc;width: 40px;text-align:center;font-size: 10px;font-weight: bold;padding-right: 2px;cursor: context-menu;" value="Pcs" disabled="">
									</td>
								</tr>
							</table>
						</th>
					{/if}
					{/section}
				</tr>
			<!--END MULTIPLE DELIVER BRANCHES-->
			
			<!--START SINGLE DELIVER BRANCHES-->
			{else}
				<tr bgcolor=#ffffff>
					<th colspan=2>#</th>
					<th nowrap>Article No</th>
					<th nowrap>MCode</th>
					{if $config.link_code_name && $config.docs_show_link_code}
						<th nowrap rowspan="2">{$config.link_code_name}</th>
					{/if}
					<th nowrap>SKU Description</th>
					<th>Sugg.<br>Selling<br>Price</th>
					{if $form.branch_is_under_gst}
						<th>Nett<br>Selling<br>Price</th>
					{/if}
					<th>Selling<br>UOM</th>
					<th>Purchase<br>UOM</th>
					<th>Cost<br>Price</th>
					<th>Cost<br>Indicate</th>
					<th nowrap>Qty<br>
						<table width="100%">
							<tr>
								<td align="center">
								<input class="tbl_col_ctn" style="background:#fff;border:1px solid #ccc;width: 40px;text-align:center;font-size: 10px;font-weight: bold;padding-right: 2px;cursor: context-menu;" value="Ctn" disabled="">
								</td>
								<td align="center">
								<input class="tbl_col_ctn" style="background:#fc9;border:1px solid #ccc;width: 40px;text-align:center;font-size: 10px;font-weight: bold;padding-right: 2px;cursor: context-menu;" class="tbl_col_pcs" value="Pcs" disabled="">
								</td>
							</tr>
						</table>
					</th>
					<th nowrap style="{if $config.po_use_simple_mode}display:none;{/if}">FOC<br>
						<table width="100%">
							<tr>
								<td align="center">
								<input class="tbl_col_ctn" style="background:#fff;border:1px solid #ccc;width: 40px;text-align:center;font-size: 10px;font-weight: bold;padding-right: 2px;cursor: context-menu;" value="Ctn" disabled="">
								</td>
								<td align="center">
								<input class="tbl_col_ctn" style="background:#fc9;border:1px solid #ccc;width: 40px;text-align:center;font-size: 10px;font-weight: bold;padding-right: 2px;cursor: context-menu;" class="tbl_col_pcs" value="Pcs" disabled="">
								</td>
							</tr>
						</table>
					</th>
					<th nowrap style="{if $config.po_use_simple_mode}display:none;{/if}">Sales Trend<br>
					<input class="tbl_col_salestrend" style="border:1px solid #ccc;background:#ccc; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="1M" disabled="">
					<input class="tbl_col_salestrend" style="border:1px solid #ccc;background:#ddd; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="3M" disabled="">
					<input class="tbl_col_salestrend" style="border:1px solid #ccc;background:#ccc; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="6M" disabled="">
					<input class="tbl_col_salestrend" style="border:1px solid #ccc;background:#ddd; width: 40px;font-size: 10px;text-align:center;font-weight: bold;cursor: context-menu;" value="12M" disabled="">		
					</th>
					<th rowspan=2>Total<br>Qty</th>
					<th rowspan="2" style="{if $config.po_use_simple_mode}display:none;{/if}">Total<br>FOC</th>
					<th>Gross<br>Amount</th>
					<th style="{if $config.po_use_simple_mode}display:none;{/if}">Tax<br>(%)</th>
					<th style="{if $config.po_use_simple_mode}display:none;{/if}">Discount</th>
					<th style="{if $config.po_use_simple_mode}display:none;{/if}">Nett<br>Amount</th>
					{if $form.is_under_gst}
						<th rowspan="2">GST<br />Code</th>
						<th rowspan="2">Amount<br />Incl. GST</th>
					{/if}
					<th>Total<br>Selling</th>
					<th {if !$sessioninfo.privilege.SHOW_REPORT_GP}style="display:none;"{/if}>Gross<br>Profit</th>
					<th>Profit(%)</th>
					
					<!-- create by sales order -->
					{if $form.po_create_type eq 1}
						<th>Sales Order</th>
					{/if}
				</tr>
			{/if}
			<!--END SINGLE DELIVER BRANCHES-->
			</thead>
			
			<tbody id="po_items">
				{assign var=count value=0}
				{foreach from=$po_items item=item name=fitem}
					{include file=po.new.po_row.tpl}
			
					{if $item.last_po}
						{assign var=l_item value=$item.last_po}
						{include file=po.new.last_po_row.tpl pi_id=$item.id}		
					{/if}
					
					{if $config.po_set_max_items}
						{assign var=count value=$count+1}
					{/if}
				{/foreach}
			</tbody>
			
			
			<tfoot id="tbl_footer">
			<tr class="normal" bgcolor="{#TB_ROWHEADER#}" id="add_sku_row" style="{if $allow_edit && $count<15 && !($config.enable_po_agreement && !$sessioninfo.privilege.PO_AGREEMENT_OPEN_BUY)} {else} display:none; {/if}">
			{if $view_hq}
				{assign var=offset value=$gst_colspan+18}
				{if $sessioninfo.privilege.SHOW_REPORT_GP}{assign var=offset value=$offset+1}{/if}
				
				<!-- create by sales order -->
				{if $form.po_create_type eq 1}
					{assign var=offset value=$offset+1}
				{/if}
				{if $config.link_code_name && $config.docs_show_link_code}
					{assign var=offset value=$offset+1}
				{/if}
				<td colspan="{count var=$form.deliver_to multi=2 offset=$offset}" nowrap>
			{else}
				{assign var=offset value=$gst_colspan+21}
				{if $form.branch_is_under_gst}
					{assign var=offset value=$offset+1}
				{/if}
				{if $sessioninfo.privilege.SHOW_REPORT_GP}{assign var=offset value=$offset+1}{/if}
				
				<!-- create by sales order -->
				{if $form.po_create_type eq 1}
					{assign var=offset value=$offset+1}
				{/if}
				{if $config.link_code_name && $config.docs_show_link_code}
					{assign var=offset value=$offset+1}
				{/if}
				<td colspan="{$offset}" nowrap>
			{/if}
				<input name="sku_item_id" size=3 type=hidden>
				<input name="sku_item_code" size=13 type=hidden>
				
				{include file='scan_barcode_autocomplete.tpl' need_hr_out_bottom=1}
				<br>
				<span id="span_loading"></span>
				<br>
				<b>Search SKU</b> <input id="autocomplete_sku" name="sku" style="width:500px;" onclick="this.select()">
				<input type=button class="btn btn-primary" value="Add" onclick="add_item()">
			
				<input type=button class="btn btn-primary" value="Add FOC" onclick="sel_foc_cost()">
					
				<input type=button class="btn btn-warning" value="Cost History" onclick="get_price_history(this)">
			
				<input type=button class="btn btn-warning" value="SKU Detail" onclick="show_sku_detail()">
				
				<input type=button class="btn btn-warning" value="Related SKU" onclick="show_related_sku()">
			
				<input type=button class="btn btn-warning" value="Add In Matrix" onclick="size_color_form()">
				
				<div id="autocomplete_sku_choices" class="autocomplete" style="height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
				<br>
				<img src=ui/pixel.gif width=40 height=1>
				<input onchange="reset_sku_autocomplete('1')" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
				<input onchange="reset_sku_autocomplete('1')" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
				<input onchange="reset_sku_autocomplete('1')" type=radio name="search_type" value="3"> ARMS Code
				<input onchange="reset_sku_autocomplete('1')" type=radio name="search_type" value="4"> Description
				<br>
				{if $sessioninfo.privilege.PO_ADD_OTHER_DEPT}
					<img src=ui/pixel.gif width=40 height=1>
					<input type=checkbox id=all_dept onclick="reset_sku_autocomplete();">
					<label for=all_dept>Search from other department</label>
				{/if}
				</td>
			</tr>
			
			<!-- total -->
			<tr bgcolor="#ffffff" class="normal" height="24">
			
				{assign var=colspan value=9}
				{if !$view_hq}
					{assign var=colspan value=$colspan+1}
					{if $form.branch_is_under_gst}
						{assign var=colspan value=$colspan+1}
					{/if}
				{/if}
				{if $form.po_option==1}{assign var=colspan value=$colspan+1}{/if}
				{if $config.link_code_name && $config.docs_show_link_code}
					{assign var=colspan value=$colspan+1}
				{/if}
				<th colspan="{$colspan}" align=right>Total</th>
			
				{assign var=colspan value=2}
				{if $config.po_use_simple_mode}{assign var=colspan value=$colspan-1}{/if}
				
				{if $view_hq}
				{section name=i loop=$branch}
					{assign var=bid value=0}
					{if (is_array($form.deliver_to) && in_array($branch[i].id,$form.deliver_to)) || $branch[i].id eq $form.po_branch_id}
						{assign var=bid value=$branch[i].id}
					{/if}
					
					{if $bid > 0 }
					<td colspan="{$colspan}">
						<b>
						T.Sell: <span id="br_sp[{$bid}]">{$total.br_sp[$bid]|number_format:2}</span>
						<br>
						T.Cost: <span id="br_cp[{$bid}]">{$total.br_cp[$bid]|number_format:2}</span>
						</b>
					</td>
					{/if}
				{/section}
				{else}
					<td colspan="{$colspan}">
						<b>
						T.Sell: <span id="br_sp">{$form.total_selling_amt|number_format:2}</span>
						<br>
						T.Cost: <span id="br_cp">{$form.subtotal_po_gross_amount|number_format:2}</span>
						</b>
					</td>
				
				{/if}
			
				<td {if $config.po_use_simple_mode}style="display:none;"{/if} >&nbsp;</td>
				{assign var=colspan value=2}
				{if $config.po_use_simple_mode}{assign var=colspan value=$colspan-1}{/if}
				<th align="left" colspan="{$colspan}">
					<span id="total_ctn">Ctn: {$total.ctn|qty_nf}</span>
					<br />
					<span id="total_pcs">Pcs: {$total.qty+$total.foc|qty_nf}</span>
				</th>
				<th align="right" id="total_gross_amount">{$form.subtotal_po_gross_amount|number_format:2}</th>
				
				{* Sub Total Nett Amount *}
				<th align="right" colspan="3" style="{if $config.po_use_simple_mode}display:none;{/if}">
					<span id="total_amount">
						{$form.subtotal_po_nett_amount|number_format:2}
					</span>
					{if $form.currency_code}
						{assign var=base_subtotal_po_nett_amount value=$form.subtotal_po_nett_amount*$form.currency_rate}
						{assign var=base_subtotal_po_nett_amount value=$base_subtotal_po_nett_amount|round2}
						
						<br />
						<span class="converted_base_amt">
							<span id="span_base_total_amount">{$base_subtotal_po_nett_amount|number_format:2}</span>*
						</span>
					{/if}
				</th>
				{if $form.is_under_gst}
					<th align="right" colspan=2 id="total_gst_amount" style="{if $config.po_use_simple_mode}display:none;{/if}">{$form.subtotal_po_amount_incl_gst|number_format:2}</th>
				{/if}
				
				{* Total Selling *}
				<th align="right">
					<span id="total_sell" {if $form.currency_code}class="converted_base_amt"{/if}>
						{$form.total_selling_amt|number_format:2}
					</span>
				</th>
				
				{* Gross Profit *}
				{if $form.currency_code}
					{assign var=total_profit value=$form.total_selling_amt-$base_subtotal_po_nett_amount}
				{else}
					{assign var=total_profit value=$form.total_selling_amt-$form.subtotal_po_nett_amount}
				{/if}
				<th align="right" style="{if !$sessioninfo.privilege.SHOW_REPORT_GP}display:none;{/if}" nowrap class="{if $form.currency_code}converted_base_amt{/if}">
					<span id="total_profit" class="{if $total_profit<=0}negative_value{/if}">{$total_profit|number_format:2}</span>{if $form.currency_code}*{/if}
				</th>
				
				<th align="right" id="total_margin" {if $total_profit<=0}style="color:#f00"{/if}>
					{if $form.total_selling_amt <= 0}-{else}{$total_profit/$form.total_selling_amt*100|number_format:2}%{/if}
				</th>
				
				<!-- create by sales order -->
				{if $form.po_create_type eq 1}
					<td>&nbsp;</td>
				{/if}
			</tr>
			
			<!-- misc cost -->
			<tr class="normal">
				{if $form.po_option eq '1'}
				<th>&nbsp;</th>
				{/if}
				{assign var=colspan value=6}
				{assign var=rowspan value=$gst_rowspan+8}
				{if $config.po_use_simple_mode}{assign var=colspan value=$colspan-2}{/if}
				{if !$view_hq}
					{if $form.branch_is_under_gst}
						{assign var=colspan value=$colspan+1}
					{/if}
				{/if}
				{if $config.link_code_name && $config.docs_show_link_code}
					{assign var=colspan value=$colspan+1}
				{/if}
				<td colspan="{$colspan}" rowspan="{$rowspan}" valign=top>
					<!--- remark box -->
					<b>Remark:</b><br>
					<textarea style="width:200px;height:80px;font:10px arial;" name="remark">{$form.remark|escape}</textarea>
				</td>
				
				{assign var=colspan value=$gst_colspan+4}
				{if $sessioninfo.privilege.SHOW_REPORT_GP}{assign var=colspan value=$colspan+1}{/if}
				{if $config.po_use_simple_mode}{assign var=colspan value=$colspan-2}{/if}
				<td colspan="{$colspan}" rowspan="{$rowspan}" valign=top>
					<b>Remark#2 (For Internal Use):</b><br>
					<textarea style="width:200px;height:80px;font:10px arial;" name="remark2">{$form.remark2|escape}</textarea>
				</td>
				{if $view_hq}
					{assign var=tmp_col_multi value=2}
					{assign var=tmp_col_offset value=3}
					{if $config.po_use_simple_mode}
						{assign var=tmp_col_multi value=$tmp_col_multi-1}
						{assign var=tmp_col_offset value=$tmp_col_offset-1}
					{/if}
					<th align="right" colspan="{count var=$form.deliver_to multi=$tmp_col_multi offset=$tmp_col_offset}">
				{else}
					{assign var=tmp_colspan value=6}
					{if $config.po_use_simple_mode}{assign var=tmp_colspan value=$tmp_colspan-1}{/if}
					<th align="right" colspan="{$tmp_colspan}">
				{/if}
					Misc Cost [<a href="javascript:void(show_markup_help())">?</a>]
					</th>
				<th align="right" colspan="2">
					+ <input size="8" id="misc_cost" name="misc_cost" value="{$form.misc_cost}" onchange="recalc_totals();{if $form.is_under_gst}calculate_all_gst();{/if}" onclick="clear0(this)">
				</th>
			</tr>
			
			<!-- final discount -->
			<tr class="normal">
			{if $form.po_option eq '1'}
			<th>&nbsp;</th>
			{/if}
			{if $view_hq}
				<th align="right" colspan="{count var=$form.deliver_to multi=$tmp_col_multi offset=$tmp_col_offset}">
			{else}
				<th align="right" colspan="{$tmp_colspan}">
			{/if}
					Discount [<a href="javascript:void(show_discount_help())">?</a>]
				</th>
				<th align="right" colspan=2>
					- <input size="8" id="sdiscount" name="sdiscount" value="{$form.sdiscount}" onchange="recalc_totals();{if $form.is_under_gst}calculate_all_gst();{/if}" onclick="clear0(this)">
				</th>
			</tr>
			
			<!-- "special" discount -->
			<tr class=normal>
			{if $form.po_option eq '1'}
			<th>&nbsp;</th>
			{/if}
			{if $view_hq}
				<th align=right colspan="{count var=$form.deliver_to multi=$tmp_col_multi offset=$tmp_col_offset}">
			{else}
				<th align=right colspan="{$tmp_colspan}">
			{/if}
					Discount from Remark#2 [<a href="javascript:void(show_discount_help())">?</a>]
				</th>
				<th align=right colspan=2>
					- <input size=8 id=rdiscount name=rdiscount value="{$form.rdiscount}" onchange="recalc_totals();{if $form.is_under_gst}calculate_all_gst();{/if}" onclick="clear0(this)">
				</th>
			</tr>
			
			<!-- "special" discount -->
			<tr class=normal>
			{if $form.po_option eq '1'}
			<th>&nbsp;</th>
			{/if}
			{if $view_hq}
				<th align=right colspan="{count var=$form.deliver_to multi=$tmp_col_multi offset=$tmp_col_offset}">
			{else}
				<th align=right colspan="{$tmp_colspan}">
			{/if}
					Deduct Cost from Remark#2 [<a href="javascript:void(show_discount_help())">?</a>]
				</th>
				<th align=right colspan=2>
					- <input size=8 id=ddiscount name=ddiscount value="{$form.ddiscount}" onchange="recalc_totals();{if $form.is_under_gst}calculate_all_gst();{/if}" onclick="clear0(this)">
				</th>
			</tr>
			
			<!-- transportation cost -->
			<tr class=normal>
			{if $form.po_option eq '1'}
			<th>&nbsp;</th>
			{/if}
			
			{if $view_hq}
				<th align=right colspan="{count var=$form.deliver_to multi=$tmp_col_multi offset=$tmp_col_offset}">
			{else}
				<th align=right colspan="{$tmp_colspan}">
			{/if}
					Transportation Charges
				</th>
				<th align=right colspan=2>
					+ <input size=8 id=transport_cost name=transport_cost value="{$form.transport_cost}" onchange="mf(this);recalc_totals();{if $form.is_under_gst}calculate_all_gst();{/if}" onclick="clear0(this)">
				</th>
			</tr>
			
			<!-- total amount -->
			<tr class="normal">
				{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
				
				{if $view_hq}
					<th align="right" colspan="{count var=$form.deliver_to multi=$tmp_col_multi offset=$tmp_col_offset}">
				{else}
					<th align="right" colspan="{$tmp_colspan}">
				{/if}
					PO Amount
				</th>
				<th align="right" colspan="2" class="large" nowrap>
					{if $form.currency_code}
						{$form.currency_code}
					{/if}
					<span id="final_amount">
						{$form.po_amount|default:0|number_format:2}
					</span>
					
					{if $form.currency_code}
						{assign var=base_po_amount value=$form.po_amount*$form.currency_rate}
						{assign var=base_po_amount value=$base_po_amount|round2}
						
						<br />
						<span class="converted_base_amt">
							{$config.arms_currency.code}
							<span id="span_base_final_amount">{$base_po_amount|number_format:2}</span>*
						</span>
					{/if}
				</th>
				
				{* Profit *}
				{if $form.currency_code}
					{assign var=final_profit value=$form.total_selling_amt-$base_po_amount}
				{else}
					{assign var=final_profit value=$form.total_selling_amt-$form.po_amount}
				{/if}
				
				<th align="right" class="large {if $form.currency_code}converted_base_amt{/if}">
					<span id="final_profit" class="{if $final_profit<=0}negative_value{/if}">{$final_profit|number_format:2}</span>{if $form.currency_code}*{/if}
				</th>
				<th align="right" id="final_margin" class="large" style="{if $final_profit<=0}color:#f00{/if}">
				{if $form.total_selling_amt}
					{$final_profit/$form.total_selling_amt*100|number_format:2}%
				{/if}
				</th>
			</tr>
			
			{if $form.is_under_gst}
				<!-- total GST amount -->
				<tr class="normal">
					{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
					
					{if $view_hq}
						<th align="right" colspan="{count var=$form.deliver_to multi=$tmp_col_multi offset=$tmp_col_offset}">
					{else}
						<th align="right" colspan="{$tmp_colspan}">
					{/if}
						PO Amount Include GST
					</th>
					<th align="right" colspan="2" id="final_gst_amount" class="large">
						{$form.po_amount_incl_gst|number_format:2}
					</th>
					{assign var=final_profit value=$form.total_gst_selling_amt-$form.po_amount_incl_gst}
					<th align="right" id="final_gst_profit" class="large" style="{if $final_profit<=0}color:#f00;{/if}{if !$form.branch_is_under_gst}display:none;{/if}">
					{$final_profit|number_format:2}
					</th>
					<th align="right" id="final_gst_margin" class="large" style="{if $final_profit<=0}color:#f00;{/if}{if !$form.branch_is_under_gst}display:none;{/if}">
					{if $form.total_gst_selling_amt}
						{$final_profit/$form.total_gst_selling_amt*100|number_format:2}%
					{/if}
					</th>
				</tr>
			{/if}
			
			<!-- supplier amount -->
			<tr class="normal">
			{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
			
			{if $view_hq}
				<th align="right" colspan="{count var=$form.deliver_to multi=$tmp_col_multi offset=$tmp_col_offset}">
			{else}
				<th align="right" colspan="{$tmp_colspan}">
			{/if}
				Supplier PO Amount
				</th>
				<th align="right" colspan="2" id="final_amount2" class="large">
					{$form.supplier_po_amt|number_format:2}
				</th>
			</tr>
			
			{if $form.is_under_gst}
				<!-- supplier GST amount -->
				<tr class="normal">
					{if $form.po_option eq '1'}<th>&nbsp;</th>{/if}
			
					{if $view_hq}
						<th align="right" colspan="{count var=$form.deliver_to multi=$tmp_col_multi offset=$tmp_col_offset}">
					{else}
						<th align="right" colspan="{$tmp_colspan}">
					{/if}
					Supplier PO Amount Include GST
					</th>
					<th align="right" colspan="2" id="final_gst_amount2" class="large">
						{$form.supplier_po_amt_incl_gst|number_format:2}
					</th>
				</tr>
			{/if}
			
			</tfoot>
			</table>
	</div>
</div>
