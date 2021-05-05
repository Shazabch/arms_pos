{*
8/13/2012 3:42 PM Andy
- Add readonly for selling price and purchase price.

8/15/2012 5:45 PM Justin
- Bug fixed on cost price always show zero.
- Added new ability to show capital alphabet before each rule number for document identity recognization purpose.

8/16/2012 9:50 AM Justin
- Enhanced to disable user to check/uncheck FOC item.

8/22/2012 11:57 AM Andy
- Fix rule alphabet running bugs.
*}

<table class="report_table" width="100%">
	<tr class="header">
		<th width="20">#</th>
		<th>P.A No</th>
		<th>Branch</th>
		<th>ARMS Code</th>
		<th>MCode</th>
		<th>Description</th>
		<th>Required Rule #</th>
		<th>Foc Qty</th>
		<th>Suggested Selling Price</th>
		<th>Purchase Price
			<br />
			<span class="span_latest_cost">Latest Cost</span>
		</th>
	</tr>
	
	<tbody id="tbody_pa_foc_item_list">
		{foreach from=$items.foc_item item=foc_pa_item name=fitem}
			<tr id="tr_foc_item_row-{$foc_pa_item.id}-{$foc_pa_item.branch_id}" class="tr_foc_item_row" >
				<td align="center" nowrap>
					<img src="ui/checked.gif" width="15" id="img_foc_item_check-{$foc_pa_item.id}-{$foc_pa_item.branch_id}" border="0" style="display:none;" title="FOC Item Checked" />
					<input type="checkbox" name="item_check[foc_item][{$foc_pa_item.id}][{$foc_pa_item.branch_id}]" value="1" style="display:none;" />
					<input type="hidden" name="sku_item_id[foc_item][{$foc_pa_item.id}][{$foc_pa_item.branch_id}]" value="{$foc_pa_item.sku_item_id}" />
					<input type="hidden" name="pa_id[foc_item][{$foc_pa_item.id}][{$foc_pa_item.branch_id}]" value="{$foc_pa_item.purchase_agreement_id}" />
				</td>
				<td>#{$foc_pa_item.purchase_agreement_id|string_format:"%05d"|default:'-'}</td>
				<td align="center">{$foc_pa_item.branch_code|default:'-'}</td>
				<td>{$foc_pa_item.sku_item_code|default:'-'}</td>
				<td>{$foc_pa_item.mcode|default:'-'}</td>
				<td>{$foc_pa_item.description|default:'-'}</td>
				
				<!-- Required Rule # -->
				<td align="center">
					<span class="span_ref_rule_num-{$foc_pa_item.purchase_agreement_id}-{$foc_pa_item.branch_id}">
						{foreach from=$foc_pa_item.ref_rule_num item=rule_num name=frn}
							{if !$smarty.foreach.frn.first}, {/if}
							{$foc_pa_item.rule_group_alp}{$rule_num}
							<input type="hidden" class="inp_ref_rule_list inp_ref_rule_item_list-{$foc_pa_item.id}-{$foc_pa_item.branch_id}  inp_ref_rule_list-{$foc_pa_item.purchase_agreement_id}-{$foc_pa_item.branch_id}-{$rule_num}" value="{$rule_num}" name="ref_rule_num[foc_item][{$foc_pa_item.id}][{$foc_pa_item.branch_id}][]" />
						{/foreach}
					</span>
				</td>
				
				<!-- Foc Qty -->
				<td align="center">
					<input type="text" name="qty[foc_item][{$foc_pa_item.id}][{$foc_pa_item.branch_id}]" size="5" value="{$foc_pa_item.qty}" class="required r" title="Qty" onChange="mfz(this);" readonly />
					<input type="hidden" name="old_qty[foc_item][{$foc_pa_item.id}][{$foc_pa_item.branch_id}]" value="{$foc_pa_item.qty}" />
				</td>
				
				<!-- Suggested Selling Price -->
				<td align="center">
					<input type="text" name="suggest_selling_price[foc_item][{$foc_pa_item.id}][{$foc_pa_item.branch_id}]" size="5" value="{$foc_pa_item.suggest_selling_price|number_format:2:".":""|ifzero:''}" class="required r" title="Suggested Selling Price" onChange="PURCHASE_AGREEMENT_MODULE.item_price_changed(this);" id="suggest_selling_price-foc_item-{$foc_pa_item.id}-{$foc_pa_item.branch_id}" readOnly />
				</td>
				
				<!-- Purchase Price  -->
				<td align="center">
					<input type="text" name="purchase_price[foc_item][{$foc_pa_item.id}][{$foc_pa_item.branch_id}]" size="5" value="{$foc_pa_item.purchase_price|number_format:$config.global_cost_decimal_points:".":""|ifzero:''}" class="required r" title="Purhcase Price" onChange="PURCHASE_AGREEMENT_MODULE.item_price_changed(this);" id="purchase_price-foc_item-{$foc_pa_item.id}-{$foc_pa_item.branch_id}" readOnly />
					<input type="hidden" name="cost[foc_item][{$foc_pa_item.id}][{$foc_pa_item.branch_id}]" size="5" value="{$foc_pa_item.cost|number_format:$config.global_cost_decimal_points:".":""}" />
					<br />
					<span class="span_latest_cost">{$foc_pa_item.cost|number_format:$config.global_cost_decimal_points}</span>
				</td>
			</tr>
			{assign var=foc_pa_id value=$foc_pa_item.purchase_agreement_id}
		{/foreach}
	</tbody>
</table>
