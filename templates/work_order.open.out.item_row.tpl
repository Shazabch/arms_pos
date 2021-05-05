{*
3/2/2021 3:44 PM Andy
- Enhanced Work Order to can transfer by Weight to Pcs.
*}

{assign var=item_id value=$item.id}

<tr id="tr_item-out-{$item_id}" class="tr_item">
	<td nowrap>
		<input type="hidden" name="items_list[out][{$item_id}][id]" value="{$item_id}">
		<input type="hidden" class="inp_sku_item_id" name="items_list[out][{$item_id}][sku_item_id]" value="{$item.sku_item_id}">
		<input type="hidden" name="items_list[out][{$item_id}][doc_allow_decimal]" value="{$item.doc_allow_decimal}">
		
		{if $can_edit and $action eq 'out'}
			<img src="ui/remove16.png" class="clickable" title="Delete Row" onclick="WO_OPEN.delete_item_clicked('{$item_id}')" align="absmiddle" id="img-delete_item-out-{$item_id}" />
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
	<td align="right">
		{$item.weight_kg}
		<input type="hidden" name="items_list[out][{$item_id}][weight_kg]" value="{$item.weight_kg}" />
	</td>
	
	{* Stock Balance *}
	<td align="right">
		{$item.stock_balance|qty_nf}
		<input type="hidden" name="items_list[out][{$item_id}][stock_balance]" value="{$item.stock_balance}" />
	</td>
	
	{* Cost *}
	<td align="right">
		{$item.cost|number_format:$config.global_cost_decimal_points}
		<input type="hidden" name="items_list[out][{$item_id}][cost]" value="{$item.cost|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
	
	{* Selling Price *}
	<td align="right">
		{assign var=price value=$item.price}
		{assign var=display_price value=$item.display_price|round:2}
		{if $display_price<=0}{assign var=display_price value=$price|round:2}{/if}
		
		{if $config.enable_gst and $form.branch_is_under_gst}
			<input type="hidden" name="items_list[out][{$item_id}][display_price_is_inclusive]" value="{$item.display_price_is_inclusive}" />
			<input type="hidden" name="items_list[out][{$item_id}][display_price]" value="{$display_price|number_format:2:'.':''}" />
			<input type="hidden" name="items_list[out][{$item_id}][gst_id]" value="{$item.gst_rate}" />
			<input type="hidden" name="items_list[out][{$item_id}][gst_code]" value="{$item.gst_code}" />
			<input type="hidden" name="items_list[out][{$item_id}][gst_rate]" value="{$item.gst_rate}" />
			
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
		
		<input type="hidden" name="items_list[out][{$item_id}][price]" value="{if $form.branch_is_under_gst}{$price}{else}{$price|number_format:2:'.':''}{/if}" />
	</td>
	
	{* Transfer Out Qty *}
	<td align="center">
		<input class="r inp_qty {if $item.doc_allow_decimal}inp_doc_allow_decimal{/if}" name="items_list[out][{$item_id}][qty]" onchange="WO_OPEN.out_qty_changed('{$item_id}');" value="{$item.qty}" {if $action ne 'out'}readonly{/if} />
	</td>
	
	{* Transfer Out Total Cost *}
	<td align="right">
		<span id="span_line_total_cost-out-{$item_id}">{$item.line_total_cost|number_format:$config.global_cost_decimal_points}</span>
		<input type="hidden" name="items_list[out][{$item_id}][line_total_cost]" value="{$item.line_total_cost|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
	
	{* Transfer Out Total Weight KG *}
	<td align="right">
		<span id="span_line_exptected_weigth-out-{$item_id}">{$item.line_exptected_weigth}</span>
		<input type="hidden" name="items_list[out][{$item_id}][line_exptected_weigth]" value="{$item.line_exptected_weigth}" />
	</td>
	
	{* Actual Transfer Out Received Weight *}
	<td align="center" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
		<input class="r inp_rcv_weight" name="items_list[out][{$item_id}][line_actual_received_weigth]" onchange="WO_OPEN.out_actual_rcv_weight_changed('{$item_id}');" value="{$item.line_actual_received_weigth}" {if $action ne 'out'}readonly{/if} />
	</td>
	
	{* Actual Transfer Out Shrinkage Weight *}
	<td align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
		<span id="span_line_shrinkage_weigth-out-{$item_id}">{$item.line_shrinkage_weigth}</span>
		<input type="hidden" name="items_list[out][{$item_id}][line_shrinkage_weigth]" value="{$item.line_shrinkage_weigth}" />
	</td>
	
	{* Actual Transfer Out Cost Per KG *}
	<td align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
		<span id="span_cost_per_weight-out-{$item_id}">{$item.cost_per_weight|number_format:$config.global_cost_decimal_points}</span>
		<input type="hidden" name="items_list[out][{$item_id}][cost_per_weight]" value="{$item.cost_per_weight|number_format:$config.global_cost_decimal_points:'.':''}" />
	</td>
</tr>