{*
3/2/2021 3:44 PM Andy
- Enhanced Work Order to can transfer by Weight to Pcs.
*}

{assign var=item_id value=$item.id}

<tr id="tr_item-in-{$item_id}" class="tr_item {if $smarty.request.highlight_in_sid eq $item.sku_item_id}highlight_row{/if}">
	<td nowrap>
		<input type="hidden" name="items_list[in][{$item_id}][id]" value="{$item_id}">
		<input type="hidden" class="inp_sku_item_id" name="items_list[in][{$item_id}][sku_item_id]" value="{$item.sku_item_id}">
		<input type="hidden" name="items_list[in][{$item_id}][doc_allow_decimal]" value="{$item.doc_allow_decimal}">
		
		{if $can_edit and $action eq 'in'}
			<img src="ui/remove16.png" class="clickable" title="Delete Row" onclick="WO_OPEN.delete_item_clicked('{$item_id}')" align="absmiddle" id="img-delete_item-in-{$item_id}" />
		{/if}

		<span class="span_item_no">{$smarty.foreach.fwoi.iteration}</span>
	</td>
	
	<td>{$item.sku_item_code}</td>
	<td>{$item.mcode|default:'-'}</td>
	<td>{$item.artno|default:'-'}</td>
	
	<td>
		{$item.description}
		{include file='details.uom.tpl' uom=$item.master_uom_code}
	</td>
	
	{* Weight *}
	<td align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
		{$item.weight_kg}
		<input type="hidden" name="items_list[in][{$item_id}][weight_kg]" value="{$item.weight_kg}" />
	</td>
	
	{* UOM *}
	<td align="right" class="col_w2p" style="{if $form.transfer_type ne 'w2p'}display:none;{/if}">
		<select name="items_list[in][{$item_id}][uom_id]" onChange="WO_OPEN.in_uom_id_changed('{$item_id}');">
			{foreach from=$uom_list key=uom_id item=uom}
				<option value="{$uom_id}" {if $item.uom_id eq $uom_id}selected {/if} fraction="{$uom.fraction}">{$uom.code}</option>
			{/foreach}
		</select>
	</td>
	
	{* Stock Balance *}
	<td align="right">
		{$item.stock_balance|qty_nf}
		<input type="hidden" name="items_list[in][{$item_id}][stock_balance]" value="{$item.stock_balance}" />
	</td>
	
	{* Cost *}
	<td align="right">
		{$item.cost|number_format:$config.global_cost_decimal_points}
		<input type="hidden" name="items_list[in][{$item_id}][cost]" value="{$item.cost|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
	
	{* Selling Price *}
	<td align="right">
		{assign var=price value=$item.price}
		{assign var=display_price value=$item.display_price|round:2}
		{if $display_price<=0}{assign var=display_price value=$price|round:2}{/if}
		
		{if $config.enable_gst and $form.branch_is_under_gst}
			<input type="hidden" name="items_list[in][{$item_id}][display_price_is_inclusive]" value="{$item.display_price_is_inclusive}" />
			<input type="hidden" name="items_list[in][{$item_id}][display_price]" value="{$display_price|number_format:2:'.':''}" />
			<input type="hidden" name="items_list[in][{$item_id}][gst_id]" value="{$item.gst_rate}" />
			<input type="hidden" name="items_list[in][{$item_id}][gst_code]" value="{$item.gst_code}" />
			<input type="hidden" name="items_list[in][{$item_id}][gst_rate]" value="{$item.gst_rate}" />
			
			{$display_price|number_format:2}
			{if $price < $display_price}
				<br />
				<span class="span_price2">
					Excl: {$price|number_format:2}
				</span>
			{/if}
		{else}
			{$price|number_format:2}
		{/if}
		
		<input type="hidden" name="items_list[in][{$item_id}][price]" value="{if $form.branch_is_under_gst}{$price}{else}{$price|number_format:2:'.':''}{/if}" />
	</td>
	
	{* Expected Transfer In Qty *}
	<td align="center" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
		<input class="r inp_qty {if $item.doc_allow_decimal}inp_doc_allow_decimal{/if}" name="items_list[in][{$item_id}][expect_qty]" onchange="WO_OPEN.in_expected_qty_changed('{$item_id}');" value="{$item.expect_qty}" {if $action ne 'in'}readonly{/if} />
	</td>
	
	{* Expected Unit Cost *}
	<td align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
		<span id="span_expect_cost-in-{$item_id}">{$item.expect_cost|number_format:$config.global_cost_decimal_points}</span>
		<input type="hidden" name="items_list[in][{$item_id}][expect_cost]" value="{$item.expect_cost|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
	
	{* Expected Total Cost *}
	<td align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
		<span id="span_line_total_expect_cost-in-{$item_id}">{$item.line_total_expect_cost|number_format:$config.global_cost_decimal_points}</span>
		<input type="hidden" name="items_list[in][{$item_id}][line_total_expect_cost]" value="{$item.line_total_expect_cost|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
	
	{* Expected Total Weight *}
	<td align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
		<span id="span_line_total_expect_weight-in-{$item_id}">{$item.line_total_expect_weight}</span>
		<input type="hidden" name="items_list[in][{$item_id}][line_total_expect_weight]" value="{$item.line_total_expect_weight|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
		
	{* Actual Transfer In ADJ Qty *}
	<td align="center" class="col_w2p" style="{if $form.transfer_type ne 'w2p'}display:none;{/if}">
		<input class="r inp_qty" name="items_list[in][{$item_id}][actual_adj_qty]" onchange="WO_OPEN.in_actual_adj_qty_changed('{$item_id}');" value="{$item.actual_adj_qty}" {if $action ne 'in'}readonly{/if} />
	</td>
	
	{* Actual Transfer In Qty *}
	<td align="center">
		<input class="r inp_qty {if $item.doc_allow_decimal}inp_doc_allow_decimal{/if}" name="items_list[in][{$item_id}][actual_qty]" onchange="WO_OPEN.in_actual_qty_changed('{$item_id}');" value="{$item.actual_qty}" {if $action ne 'in' or $form.transfer_type eq 'w2p'}readonly{/if} />
	</td>
	
	{* Actual Unit Cost *}
	<td align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
		<span id="span_actual_cost-in-{$item_id}">{$item.actual_cost|number_format:$config.global_cost_decimal_points}</span>
		<input type="hidden" name="items_list[in][{$item_id}][actual_cost]" value="{$item.actual_cost|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
	
	{* Actual Total Cost *}
	<td align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
		<span id="span_line_total_actual_cost-in-{$item_id}">{$item.line_total_actual_cost|number_format:$config.global_cost_decimal_points}</span>
		<input type="hidden" name="items_list[in][{$item_id}][line_total_actual_cost]" value="{$item.line_total_actual_cost|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
	
	{* Actual Total Weight *}
	<td align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
		<span id="span_line_total_actual_weight-in-{$item_id}">{$item.line_total_actual_weight}</span>
		<input type="hidden" name="items_list[in][{$item_id}][line_total_actual_weight]" value="{$item.line_total_actual_weight|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
	
	{* Finish Cost - Unit *}
	<td align="right">
		<span id="span_finish_cost-in-{$item_id}">{$item.finish_cost|number_format:$config.global_cost_decimal_points}</span>
		<input type="hidden" name="items_list[in][{$item_id}][finish_cost]" value="{$item.finish_cost|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
	
	{* Finish Cost - Total *}
	<td align="right">
		<span id="span_line_total_finish_cost-in-{$item_id}">{$item.line_total_finish_cost|number_format:$config.global_cost_decimal_points}</span>
		<input type="hidden" name="items_list[in][{$item_id}][line_total_finish_cost]" value="{$item.line_total_finish_cost|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
	
	{* Finish Cost - GP *}
	<td align="right">
		<span id="span_line_total_finish_gp-in-{$item_id}" class="{if $item.line_total_finish_gp <= 0}negative_value{/if}">{$item.line_total_finish_gp|number_format:$config.global_cost_decimal_points}</span>
	</td>
	
	{* Finish Cost - GP Percent *}
	<td align="right">
		<span id="span_line_total_finish_gp_per-in-{$item_id}" class="{if $item.line_total_finish_gp_per <= 0}negative_value{/if}">{$item.line_total_finish_gp_per|number_format:2}</span>
	</td>
</tr>