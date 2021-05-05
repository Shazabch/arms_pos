{*
3/2/2021 3:44 PM Andy
- Enhanced Work Order to can transfer by Weight to Pcs.
*}

<div class="stdframe">
	<h4><img src="ui/icons/basket_delete.png" align="top"> TRANSFER OUT</h4>
	
	<table width="100%" style="border:1px solid #999; padding:5px; background-color:#fe9" cellspacing="1" cellpadding="1">
		<tr bgcolor="#ffffff">
			<th width="20" rowspan="2">#</th>
			<th rowspan="2">ARMS Code</th>
			<th rowspan="2">MCode</th>
			<th rowspan="2">Art No</th>
			<th rowspan="2">Description</th>
			<th rowspan="2">Weight (KG)</th>
			<th rowspan="2">Stock<br />Balance</th>
			<th rowspan="2">Cost</th>
			<th rowspan="2">Selling<br />Price</th>
			<th colspan="3">Transfer Out</th>
			<th colspan="3" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Actual Transfer Out</th>
		</tr>
		
		<tr bgcolor="#ffffff">
			{* Transfer Out *}
			<th>Qty</th>
			<th>Total<br />Cost</th>
			<th>Total<br />Weight (KG)</th>
			
			{* Actual Transfer Out *}
			<th class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Received<br />Weight (KG)</th>
			<th class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Shrinkage<br />Weight (KG)</th>
			<th class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Cost per KG</th>
		</tr>
		
		<tbody id="tbody_items-out">
			{foreach from=$items_list.out item=item name=fwoi}
				{include file='work_order.open.out.item_row.tpl'}
			{/foreach}
		</tbody>
		
		<tfoot>
			<tr bgcolor="#ffffff" height="24">
				<th colspan="9" align="right">Total</th>
				
				{* Total Qty *}
				<th align="right">
					<span id="span_out_total_qty">{$form.out_total_qty|qty_nf}</span>
					<input type="hidden" name="out_total_qty" value="{$form.out_total_qty}" />
				</th>
				
				{* Total Cost *}
				<th align="right">
					<span id="span_out_total_cost">{$form.out_total_cost|number_format:$config.global_cost_decimal_points}</span>
					<input type="hidden" name="out_total_cost" value="{$form.out_total_cost|number_format:$config.global_cost_decimal_points:'.':''}" />
				</th>
				
				{* Total Weight *}
				<th align="right">
					<span id="span_out_total_weight">{$form.out_total_weight|qty_nf}</span>
					<input type="hidden" name="out_total_weight" value="{$form.out_total_weight}" />
				</th>
				
				{* Total Received Weight *}
				<th align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
					<span id="span_out_actual_received_weight">{$form.out_actual_received_weight|qty_nf}</span>
					<input type="hidden" name="out_actual_received_weight" value="{$form.out_actual_received_weight}" />
				</th>
				
				{* Total Shrinkage Weight *}
				<th align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
					<span id="span_out_shrinkage_weight">{$form.out_shrinkage_weight|qty_nf}</span>
					<input type="hidden" name="out_shrinkage_weight" value="{$form.out_shrinkage_weight}" />
				</th>
				
				{* Final Cost Per KG *}
				<th align="right" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
					<span id="span_out_actual_cost_per_kg">{$form.out_actual_cost_per_kg|number_format:$config.global_cost_decimal_points}</span>
					<input type="hidden" name="out_actual_cost_per_kg" value="{$form.out_actual_cost_per_kg|number_format:$config.global_cost_decimal_points:'.':''}" />
				</th>
			</tr>
		</tfoot>
	</table>
</div>