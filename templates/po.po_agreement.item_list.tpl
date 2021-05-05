{*
8/13/2012 3:42 PM Andy
- Add readonly for selling price and purchase price.

8/14/2012 3:27 PM Andy
- Enhance Qty Type label to follow global array value.
- Enhanced to show multiply wording and the multiplier method.

8/15/2012 5:45 PM Justin
- Added new ability to show capital alphabet before each rule number for document identity recognization purpose.

8/22/2012 11:57 AM Andy
- Fix rule alphabet running bugs.

7/5/2016 4:17 PM Andy
- Enhanced to able to create from tmp_purchase_agreement_info.

9/8/2016 4:23 PM Andy
- Enhanced to have remark for purchase agreement.
- Enhanced to bring purchase agreement remark to po when the item was selected.
*}

<table class="report_table" width="100%">
	<tr class="header">
		<th width="20">#</th>
		<th>P.A No</th>
		<th>Branch</th>
		<th>ARMS Code</th>
		<th>MCode</th>
		<th>Description</th>
		<th>Rule #</th>
		<th>Qty Type</th>
		<th>Qty</th>
		<th>Discount [<a href="javascript:void(discount_help())">?</a>]</th>
		<th>Suggested Selling Price</th>
		<th>Purchase Price
			<br />
			<span class="span_latest_cost">Latest Cost</span>
		</th>
		<th>GP %</th>
	</tr>
	<tbody id="tbody_pa_item_list">
		{foreach from=$items.item item=pa_item name=fitem}
			<tr id="tr_item_row-{$pa_item.branch_id}-{$pa_item.id}" class="tr_item_row tr_item_rule_group-{$pa_item.rule_group_alp}" rule_group_alp="{$pa_item.rule_group_alp}">
				<td align="center" nowrap>
					<input type="checkbox" name="item_check[item][{$pa_item.id}][{$pa_item.branch_id}]" id="item_check-{$pa_item.id}-{$pa_item.branch_id}" value="1" onclick="PURCHASE_AGREEMENT_MODULE.check_foc_items('{$pa_item.purchase_agreement_id}', '{$pa_item.id}', '{$pa_item.branch_id}');" class="chx_rule-{$pa_item.purchase_agreement_id}-{$pa_item.branch_id}-{$pa_item.rule_num} item_checkbox item_checkbox_rule_group-{$pa_item.rule_group_alp}" {if $pa_item.checked}checked {/if} />
					
					<input type="hidden" name="sku_item_id[item][{$pa_item.id}][{$pa_item.branch_id}]" value="{$pa_item.sku_item_id}" />
					<input type="hidden" name="pa_id[item][{$pa_item.id}][{$pa_item.branch_id}]" value="{$pa_item.purchase_agreement_id}" />
				</td>
				<td>#{$pa_item.purchase_agreement_id|string_format:"%05d"|default:'-'}</td>
				<td align="center">{$pa_item.branch_code|default:'-'}</td>
				<td>{$pa_item.sku_item_code|default:'-'}</td>
				<td>{$pa_item.mcode|default:'-'}</td>
				<td>{$pa_item.description|default:'-'}</td>
				
				<!-- rule num -->
				<td align="center">
					<input type="hidden" name="rule_num[item][{$pa_item.id}][{$pa_item.branch_id}]" value="{$pa_item.rule_num}" class="inp_rule_num-{$pa_item.id}-{$pa_item.branch_id}" id="inp_rule_num-{$pa_item.id}-{$pa_item.branch_id}" />
					{$pa_item.rule_group_alp}{$pa_item.rule_num|default:'-'}
				</td>
				
				<!-- Qty Type -->
				<td align="center">
					{$pa_qty_type_list[$pa_item.qty_type]}
					{if $pa_item.qty_type eq 'range'}
						<br />
						<span class="span_latest_cost">(From {$pa_item.qty1} To {$pa_item.qty2})</span>
					{elseif $pa_item.qty_type eq 'multiply'}
						<br />
						<span class="span_latest_cost">(Qty multiply by {$pa_item.qty1})</span>
					{/if}
				</td>
				
				<!-- Qty -->
				<td align="center" nowrap>
					<input type="text" name="qty[item][{$pa_item.id}][{$pa_item.branch_id}]" size="5" value="{$pa_item.qty|default:$pa_item.qty1}" class="required r" title="Qty" onchange="mfz(this); PURCHASE_AGREEMENT_MODULE.qty_changed('{$pa_item.purchase_agreement_id}', '{$pa_item.id}', '{$pa_item.branch_id}', this);" id="inp-qty1-item-{$pa_item.id}-{$pa_item.branch_id}" {if $pa_item.qty_type ne 'range' && $pa_item.qty_type ne 'multiply'}readonly{/if} />
					<input type="hidden" name="qty_type[item][{$pa_item.id}][{$pa_item.branch_id}]" value="{$pa_item.qty_type}" />
					<input type="hidden" name="qty1[item][{$pa_item.id}][{$pa_item.branch_id}]" value="{$pa_item.qty1}" />
					<input type="hidden" name="qty2[item][{$pa_item.id}][{$pa_item.branch_id}]" value="{$pa_item.qty2}" />
				</td>
				
				<!-- Discount -->
				<td align="center">
					<input type="text" name="discount[item][{$pa_item.id}][{$pa_item.branch_id}]" size="8" value="{$pa_item.discount}" onchange="PURCHASE_AGREEMENT_MODULE.item_discount_changed('{$pa_item.id}', '{$pa_item.branch_id}');" id="inp_discount-item-{$pa_item.id}-{$pa_item.branch_id}" class="r" readonly />
				</td>
				
				<!-- Suggested Selling Price -->
				<td align="center">
					<input type="text" name="suggest_selling_price[item][{$pa_item.id}][{$pa_item.branch_id}]" size="5" value="{$pa_item.suggest_selling_price|number_format:2:".":""|ifzero:''}" class="required r" title="Suggested Selling Price" onchange="PURCHASE_AGREEMENT_MODULE.item_price_changed(this);" id="suggest_selling_price-item-{$pa_item.id}-{$pa_item.branch_id}" readOnly />
				</td>
				
				<!-- Purchase Price -->
				<td align="center">
					<input type="text" name="purchase_price[item][{$pa_item.id}][{$pa_item.branch_id}]" size="5" value="{$pa_item.purchase_price|number_format:$config.global_cost_decimal_points:".":""|ifzero:''}" class="required r" title="Purhcase Price" onchange="PURCHASE_AGREEMENT_MODULE.item_price_changed(this);" id="purchase_price-item-{$pa_item.id}-{$pa_item.branch_id}" readOnly />
					<input type="hidden" name="cost[item][{$pa_item.id}][{$pa_item.branch_id}]" size="5" value="{$pa_item.cost|number_format:$config.global_cost_decimal_points:".":""}" />
					<br />
					<span class="span_latest_cost">{$pa_item.cost|number_format:$config.global_cost_decimal_points}</span>
				</td>
				
				<!-- GP % -->
				<td class="r">
					{assign var=gp value=0}
					{if $pa_item.suggest_selling_price}
						{assign var=gp value=$pa_item.suggest_selling_price-$pa_item.purchase_price}
						{assign var=gp value=$gp/$pa_item.suggest_selling_price*100}
					{/if}
					
					<span id="span_gp_per-item-{$pa_item.id}-{$pa_item.branch_id}">{$gp|number_format:2}</span>%
				</td>
			</tr>
			{assign var=pa_id value=$pa_item.purchase_agreement_id}
		{/foreach}
	</tbody>
</table>
