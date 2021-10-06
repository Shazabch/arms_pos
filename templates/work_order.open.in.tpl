{*
4/5/2018 11:10 AM Andy
- Hide "Auto Fill Remaining" when in view mode.

3/2/2021 3:44 PM Andy
- Enhanced Work Order to can transfer by Weight to Pcs.
*}

{assign var=show_transfer_in value=0}
{if $form.status eq 1}
	{if $can_edit || $form.in_transfer_updated}
		{assign var=show_transfer_in value=1}
	{/if}
{/if}
<div class="stdframe" {if !$show_transfer_in}style="display:none;"{/if}>
	<h4><img src="ui/icons/basket_add.png" align="top"> TRANSFER IN</h4>
	
	<div class="table-responsive">
		<table width="100%" class="report_table table mb-0 text-md-nowrap  table-hover">
			<tr bgcolor="#ffffff">
				<th width="20" rowspan="2">#</th>
				<th rowspan="2">ARMS Code</th>
				<th rowspan="2">MCode</th>
				<th rowspan="2">Art No</th>
				<th rowspan="2">Description</th>
				<th rowspan="2" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Weight (KG)</th>
				<th rowspan="2" class="col_w2p" style="{if $form.transfer_type ne 'w2p'}display:none;{/if}">UOM</th>
				<th rowspan="2">Stock<br />Balance</th>
				<th rowspan="2">Cost</th>
				<th rowspan="2">Selling<br />Price</th>
				<th colspan="4" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Expected Transfer In</th>
				<th colspan="{if $form.transfer_type eq 'w2w'}4{elseif $form.transfer_type eq 'w2p'}2{/if}">Actual Transfer In</th>
				<th colspan="4">Finish Cost</th>
			</tr>
			<tr bgcolor="#ffffff">
				{* Expected Transfer In *}
				<th class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Qty</th>
				<th class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Unit Cost</th>
				<th class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Total Cost</th>
				<th class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Total Weight (KG)</th>
				
				{* Actual Transfer In *}
				{if $form.transfer_type eq 'w2p'}
					<th>Adj Qty</th>
				{/if}
				<th>Qty</th>
				<th class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Unit Cost</th>
				<th class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Total Cost</th>
				<th class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">Total Weight (KG)</th>
				
				{* Finish Cost *}
				<th>Unit</th>
				<th>Total</th>
				<th>GP</th>
				<th>GP %</th>
			</tr>
			
			<tbody id="tbody_items-in">
				{foreach from=$items_list.in item=item name=fwoi}
					{include file='work_order.open.in.item_row.tpl'}
				{/foreach}
			</tbody>
			
			{assign var=colspan_for_back_empty value=4}
			<tfoot>
				{* Total Weight *}
				<tr bgcolor="#ffffff" height="24" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
					{assign var=colspan value=12}
					<th colspan="{$colspan}" align="right">{* Expected Total Weight (KG) *}</th>
					
					{* Expected Total Weight (KG) *}
					<th align="right" >
						<span id="span_in_total_expect_weight">{$form.in_total_expect_weight}</span>
						<input type="hidden" name="in_total_expect_weight" value="{$form.in_total_expect_weight}" />
						<input type="hidden" name="in_total_expect_qty" value="{$form.in_total_expect_qty}" />
						
						{* Expected Cost per KG *}
						<input type="hidden" name="expect_cost_per_kg" value="{$form.expect_cost_per_kg}" />
					</th>
					
					<th colspan="3" align="right">{* Actual Total Weight (KG) *}</th>
					
					{* Actual Total Weight (KG) *}
					<th align="right">
						<span id="span_in_total_actual_weight">{$form.in_total_actual_weight}</span>
						<input type="hidden" name="in_total_actual_weight" value="{$form.in_total_actual_weight}" />
						<input type="hidden" name="in_total_actual_qty" value="{$form.in_total_actual_qty}" />
						
						{* Actual Cost per KG *}
						<input type="hidden" name="actual_cost_per_kg" value="{$form.actual_cost_per_kg}" />
					</th>
					<th colspan="{$colspan_for_back_empty}">&nbsp;</th>
				</tr>
				
				{* Shrinkage *}
				<tr bgcolor="#ffffff" height="24" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
					<th colspan="12" align="right">Remaining Loose Pack (KG)
						{if $can_edit}
							<img src="ui/icons/arrow_divide.png" align="absmiddle" title="Auto Fill Remaining" class="clickable" onClick="WO_OPEN.fill_expected_shrinkage_clicked();" />
						{/if}
					</th>
					
					{* Expected Remaing Weight / Shrinkage (KG) *}
					<th align="right">
						<span id="span_expect_shrinkage_weight">{$form.expect_shrinkage_weight}</span>
						<input type="hidden" name="expect_shrinkage_weight" value="{$form.expect_shrinkage_weight}" />
					</th>
					
					<th colspan="3" align="right">Actual Shrinkage (KG)</th>
					
					{* Remaing Weight / Shrinkage (KG) *}
					<th align="right">
						<span id="span_shrinkage_weight">{$form.shrinkage_weight}</span>
						<input type="hidden" name="shrinkage_weight" value="{$form.shrinkage_weight}" />
					</th>
					<th colspan="{$colspan_for_back_empty}">&nbsp;</th>
				</tr>
				
				{* Total Item Qty *}
				<tr bgcolor="#ffffff" height="24" class="col_w2p" style="{if $form.transfer_type ne 'w2p'}display:none;{/if}">
					<th colspan="13" align="right">Total Item Qty</th>
					<th align="right">
						<span id="span_in_total_actual_qty">{$form.in_total_actual_qty|qty_nf}</span>
					</th>
					<th colspan="{$colspan_for_back_empty}">&nbsp;</th>
				</tr>
				
				{* Total Item Cost *}
				<tr bgcolor="#ffffff" height="24">
					{assign var=colspan value=16}
					{if $form.transfer_type eq 'w2p'}{assign var=colspan value=$colspan-3}{/if}
					<th colspan="{$colspan}" align="right">Total Item Cost</th>
					<th align="right">
						<span id="span_in_total_actual_cost">{$form.in_total_actual_cost|number_format:$config.global_cost_decimal_points:'.':''}</span>
						<input type="hidden" name="in_total_actual_cost" value="{$form.in_total_actual_cost}" />
						<input type="hidden" name="in_total_expect_cost" value="{$form.in_total_expect_cost}" />
					</th>
					<th colspan="{$colspan_for_back_empty}">&nbsp;</th>
				</tr>
				
				{* Labour Cost *}
				<tr bgcolor="#ffffff" height="24">
					{assign var=colspan value=16}
					{if $form.transfer_type eq 'w2p'}{assign var=colspan value=$colspan-3}{/if}
					<th colspan="{$colspan}" align="right">Labour Cost</th>
					<th align="right">
						<input type="text" name="labour_cost" value="{$form.labour_cost}" class="inp_other_cost" {if $action ne 'in'}readonly{/if} onChange="WO_OPEN.labour_cost_changed();" />
					</th>
					<th colspan="{$colspan_for_back_empty}">&nbsp;</th>
				</tr>
				
				{* Packing Material *}
				<tr bgcolor="#ffffff" height="24">
					{assign var=colspan value=16}
					{if $form.transfer_type eq 'w2p'}{assign var=colspan value=$colspan-3}{/if}
					<th colspan="{$colspan}" align="right">Packing Material Cost</th>
					<th align="right">
						<input type="text" name="packaging_cost" value="{$form.packaging_cost}" class="inp_other_cost" {if $action ne 'in'}readonly{/if} onChange="WO_OPEN.packing_cost_changed();" />
					</th>
					<th colspan="{$colspan_for_back_empty}">&nbsp;</th>
				</tr>
				
				{* Total Cost *}
				<tr bgcolor="#ffffff" height="24">
					{assign var=colspan value=16}
					{if $form.transfer_type eq 'w2p'}{assign var=colspan value=$colspan-3}{/if}
					<th colspan="{$colspan}" align="right">Total Cost</th>
					<th align="right">
						<span id="span_total_cost">{$form.total_cost|number_format:$config.global_cost_decimal_points:'.':''}</span>
						<input type="hidden" name="total_cost" value="{$form.total_cost}" />
					</th>
					<th colspan="{$colspan_for_back_empty}">&nbsp;</th>
				</tr>
				
				{* Final Cost per KG *}
				<tr bgcolor="#ffffff" height="24" class="col_w2w" style="{if $form.transfer_type ne 'w2w'}display:none;{/if}">
					<th colspan="16" align="right">Final Cost per KG</th>
					<th align="right">
						<span id="span_final_cost_per_kg">{$form.final_cost_per_kg|number_format:$config.global_cost_decimal_points:'.':''}</span>
						<input type="hidden" name="final_cost_per_kg" value="{$form.final_cost_per_kg}" />
					</th>
					<th colspan="{$colspan_for_back_empty}">&nbsp;</th>
				</tr>
				
				{* Final Cost per Qty *}
				<tr bgcolor="#ffffff" height="24" class="col_w2p" style="{if $form.transfer_type ne 'w2p'}display:none;{/if}">
					<th colspan="13" align="right">Final Cost per Qty</th>
					<th align="right">
						<span id="span_final_cost_per_qty">{$form.final_cost_per_qty|number_format:$config.global_cost_decimal_points:'.':''}</span>
						<input type="hidden" name="final_cost_per_qty" value="{$form.final_cost_per_qty}" />
					</th>
					<th colspan="{$colspan_for_back_empty}">&nbsp;</th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>