{*
10/27/2015 4:11 PM Andy
- Enhanced to put nowrap for reason element.

5/29/2017 16:50 Qiu Ying
- Enhanced to return multiple invoice

6/16/2017 09:14 AM Qiu Ying
- Bug fixed on DO item info always scroll to top when click on "Show DO Item"

7/20/2017 9:37 AM Qiu Ying
- Bug fixed on should show artno if there is any artno existed else show mcode

2017-08-21 11:17 AM Qiu Ying
- Enhanced to load the item price, uom and gst code from DO invoice when add item

06/22/2020 04:26 PM Sheila
- Fixed table boxes alignment and width.
*}
*}

{assign var=item_id value=$cn_item.id}

<tr id="tr_cn_item-{$item_id}" class="tr_cn_item">
	<td nowrap>
		<input type="hidden" name="items_list[{$item_id}][id]" value="{$cn_item.id}">
		<input type="hidden" class="sku_items_list" name="items_list[{$item_id}][sku_item_id]" value="{$cn_item.sku_item_id}">
		<input type="hidden" class="sku_items_list" name="items_list[{$item_id}][doc_allow_decimal]" value="{$cn_item.doc_allow_decimal}">
		<input type="hidden" name="items_list[{$item_id}][do_item_id]" value="{$cn_item.do_item_id}">
		<input type="hidden" name="items_list[{$item_id}][do_total_qty]" value="{$cn_item.do_total_qty}">
		
		{if $can_edit}
			<img src="ui/remove16.png" class="clickable" title="Delete Row" onclick="CNOTE_OPEN.delete_item_clicked('{$item_id}')" align="absmiddle" id="img-delete_item-{$item_id}" />
		{/if}

		<a href="javascript:void(0)" title="Show DO item" onclick="CNOTE_OPEN.show_do_item(this,{$item_id})"><img src="ui/view.png" class="clickable" align="absmiddle"/></a>
		<span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<span class="span_item_no">{$smarty.foreach.fcni.iteration}</span>



	</td>
	<td>{$cn_item.sku_item_code}</td>
	<td>{$cn_item.artno|default:$cn_item.mcode}</td>
	<td>
		{$cn_item.description}
		{include file='details.uom.tpl' uom=$cn_item.master_uom_code}

	</td>
	
	{if $sessioninfo.privilege.SHOW_COST}
		{* Cost *}
		<td class="r">
			<input type="text" name="items_list[{$item_id}][cost]" size="6" readonly value="{$cn_item.cost|number_format:$config.global_cost_decimal_points}" class="r" {if !$can_edit}disabled{/if} />
		</td>
	{/if}
	
	{* Price *}
	<td class="r">
		{assign var=price value=$cn_item.price}
		{assign var=display_price value=$cn_item.display_price|round:2}
		{if $display_price<=0}{assign var=display_price value=$price|round:2}{/if}
		
		{if $form.is_under_gst}
			<div style="white-space: nowrap;" class="small">
				<input type="checkbox" name="items_list[{$item_id}][display_price_is_inclusive]" value="1" title="Ticked = Price is Inclusive Tax" onChange="CNOTE_OPEN.display_price_is_inclusive_changed('{$item_id}');" 
				{if $cn_item.display_price_is_inclusive}checked {/if} {if !$can_edit}disabled{/if} />
				<span>
					<input size="6" name="items_list[{$item_id}][display_price]" onChange="CNOTE_OPEN.display_price_changed('{$item_id}');" class="r" {if !$can_edit}disabled{/if}
					value="{$display_price|number_format:2:'.':''}" />
				</span>
			</div>
		{/if}
		
		<div id="div_price_info-{$item_id}" style="{if $form.is_under_gst && !$cn_item.display_price_is_inclusive}display:none;{/if}">
			<input class="r" name="items_list[{$item_id}][price]" value="{if $form.is_under_gst}{$price}{else}{$price|number_format:2:'.':''}{/if}" size="6" {if !$can_edit}disabled{/if} type="{if $form.is_under_gst}hidden{else}text{/if}" onChange="CNOTE_OPEN.price_changed('{$item_id}', true);">
			<span id="span_price_label-{$item_id}" class="small" style="color:blue;{if !$form.is_under_gst}display:none;{/if}">{$price|number_format:2:'.':''}</span>
		</div>
	</td>
	
	{* UOM *}
	<td align="center">
		<input type="hidden" name="items_list[{$item_id}][uom_id]" value="{$cn_item.uom_id|default:1}">
		<input type="hidden" name="items_list[{$item_id}][uom_fraction]" value="{$cn_item.uom_fraction|default:1}">
		<select name="items_list[{$item_id}][sel_uom]" onchange="CNOTE_OPEN.item_uom_changed('{$item_id}');" {if !$can_edit}disabled{/if}>
			{foreach from=$uomList key=uom_id item=uom}
				<option value="{$uom.id},{$uom.fraction}" {if $cn_item.uom_id eq $uom.id or ($cn_item.uom_id==0 and $uom.code eq 'EACH')}selected{/if}>{$uom.code}</option>
			{/foreach}
		</select>
	</td>
	
	{* CTN *}
	<td align="center" nowrap>
		<input style="width: 40px;text-align:center;" class="r inp_qty_ctn {if $cn_item.doc_allow_decimal}inp_doc_allow_decimal{/if}"name="items_list[{$item_id}][ctn]" {if $cn_item.uom_fraction == 1 or $cn_item.uom_id==1 or !$cn_item.uom_id}disabled value="-"{else} value="{$cn_item.ctn}"{/if} onchange="CNOTE_OPEN.item_qty_changed('{$item_id}', 'ctn');" {if !$can_edit}disabled{/if} />
	</td>
	
	{* PCS *}
	<td align="center" nowrap>
		<input style="width: 40px;text-align:center;"  class="r inp_qty_pcs {if $cn_item.doc_allow_decimal}inp_doc_allow_decimal{/if}" name="items_list[{$item_id}][pcs]" onchange="CNOTE_OPEN.item_qty_changed('{$item_id}', 'pcs');" value="{$cn_item.pcs}" {if !$can_edit}disabled{/if}>
	</td>

	{* Total Qty *}
	<td class="r">
		<span id="span_line_qty-{$item_id}" class="span_line_qty">{$cn_item.total_qty|qty_nf}</span>
	</td>
	
	{if $form.is_under_gst}
		{* Gross Amt *}
		<td class="r"><span id="span_line_gross_amt-{$item_id}">{$cn_item.line_gross_amt|number_format:2}</span></td>
		
		{* GST Code *}
		<td align="center">
			<select name="items_list[{$item_id}][item_gst]" onChange="CNOTE_OPEN.item_gst_changed('{$item_id}');" {if !$can_edit}disabled{/if}>
				{foreach from=$gst_list item=gst}
					<option value="{$gst.id}" gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" {if $cn_item.gst_id eq $gst.id and $cn_item.gst_code eq $gst.code and $cn_item.gst_rate eq $gst.rate}selected {/if}>
						{$gst.code} ({$gst.rate}%)
					</option>
				{/foreach}
			</select>
			
			<input type="hidden" name="items_list[{$item_id}][gst_id]" value="{$cn_item.gst_rate}" />
			<input type="hidden" name="items_list[{$item_id}][gst_code]" value="{$cn_item.gst_code}" />
			<input type="hidden" name="items_list[{$item_id}][gst_rate]" value="{$cn_item.gst_rate}" />
		</td>
		
		{* GST Amt *}
		<td class="r">
			<span id="span-line_gst_amt-{$item_id}">
				{$cn_item.line_gst_amt|number_format:2}
			</span>
		</td>
	{/if}
	
	{* Total Amt included gst *}
	<td class="r">
		<input type="hidden" name="items_list[{$item_id}][line_gross_amt]" value="{$cn_item.line_gross_amt}" />
		<input type="hidden" name="items_list[{$item_id}][line_gst_amt]" value="{$cn_item.line_gst_amt}" />
		<input type="text" class="r" size="6" id="span-line_amt-{$item_id}" name="items_list[{$item_id}][line_amt]" value="{$cn_item.line_amt|number_format:2}" {if !$can_edit}disabled{/if} onChange="CNOTE_OPEN.amount_changed('{$item_id}', true);"/>
		<input type="hidden" name="items_list[{$item_id}][item_discount_amount]" value="{$cn_item.item_discount_amount}" />
		{* Amt 2 *}
		<input type="hidden" name="items_list[{$item_id}][line_gross_amt2]" value="{$cn_item.line_gross_amt2}" />
		<input type="hidden" name="items_list[{$item_id}][line_gst_amt2]" value="{$cn_item.line_gst_amt2}" />
		<input type="hidden" name="items_list[{$item_id}][line_amt2]" value="{$cn_item.line_amt2}" />
		<input type="hidden" name="items_list[{$item_id}][item_discount_amount2]" value="{$cn_item.item_discount_amount2}" />
	</td>
	
	{* Reason *}
	<td nowrap>
		<input name="items_list[{$item_id}][reason]" size="10" onChange="CNOTE_OPEN.item_reason_changed('{$item_id}');" value="{$cn_item.reason}" maxlength="20">
		{if $can_edit}<img src="/ui/option_button.jpg" style="border:1px solid #bad3fc;padding:1px;" align="top" onclick="CNOTE_OPEN.show_reason_option('{$item_id}');">{/if}
		<div id="div-item_reason-{$item_id}"></div>
	</td>
	{if !$form.do_id}
	<td>
		<input type="hidden" name="items_list[{$item_id}][return_do_id]" value="{$cn_item.return_do_id}" />
		<input type="type" class="return_inv_no" name="items_list[{$item_id}][return_inv_no]" value="{$cn_item.return_inv_no}" size="12" onchange="uc(this); CNOTE_OPEN.invoice_no_changed(this,'{$item_id}')" />
	</td>
	<td>
		<input type="type" name="items_list[{$item_id}][return_inv_date]" value="{if $cn_item.return_inv_date != '0000-00-00'}{$cn_item.return_inv_date}{/if}" size="8" readonly />
	</td>
	{/if}
</tr>
