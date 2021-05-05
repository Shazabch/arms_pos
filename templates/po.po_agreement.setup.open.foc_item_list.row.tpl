{*
8/15/2012 5:45 PM Justin
- Bug fixed on cost price always show zero.

8/17/2012 4:08 PM Andy
- Change allowed branches list to edit in popup

9/24/2012 11:08 AM Andy
- Add artno.
*}

<tr id="tr_foc_item_row-{$foc_pa_item.id}" class="tr_foc_item_row">
	<td nowrap>
		<span class="span_no">{$smarty.foreach.fitem.iteration}</span>
		<input type="hidden" name="sku_item_id[foc_item][{$foc_pa_item.id}]" value="{$foc_pa_item.sku_item_id}" />
		
		{if $allow_edit}
			<img src="/ui/del.png" align="absmiddle" title="Delete" class="clickable" onClick="PURCHASE_AGREEMENT_MODULE.delete_foc_item('{$foc_pa_item.id}');" />
		{/if}
	</td>
	<td>{$foc_pa_item.sku_item_code|default:'-'}</td>
	<td>{$foc_pa_item.mcode|default:'-'}</td>
	<td>{$foc_pa_item.artno|default:'-'}</td>
	<td>{$foc_pa_item.description|default:'-'}</td>
	
	<!-- Required Rule # -->
	<td>
		{*<input id="inp_tmp_ref_rule_num-{$foc_pa_item.id}" style="display:none;" name="ref_rule_num[foc_item][{$foc_pa_item.id}][__RULE_NUM__]" value="__RULE_NUM__" />*}
		
		<span id="span_ref_rule_num-{$foc_pa_item.id}">
			{foreach from=$foc_pa_item.ref_rule_num item=rule_num name=frn}
				{if !$smarty.foreach.frn.first}, {/if}
				{$rule_num}
				<input id="inp_ref_rule_num-{$foc_pa_item.id}" style="display:none;" name="ref_rule_num[foc_item][{$foc_pa_item.id}][{$rule_num}]" value="{$rule_num}" class="inp_ref_rule_num-{$rule_num}" />
			{/foreach}
		</span>
	</td>
	
	<!-- Foc Qty -->
	<td align="center">
		<input type="text" name="qty[foc_item][{$foc_pa_item.id}]" size="5" value="{$foc_pa_item.qty}" class="required" title="Qty" onChange="PURCHASE_AGREEMENT_MODULE.foc_qty_changed(this);" />
	</td>
	
	<!-- Suggested Selling Price -->
	<td align="center">
		<input type="text" name="suggest_selling_price[foc_item][{$foc_pa_item.id}]" size="5" value="{$foc_pa_item.suggest_selling_price|number_format:2:".":""|ifzero:''}" class="required" title="Suggested Selling Price" onChange="PURCHASE_AGREEMENT_MODULE.item_price_changed(this);" id="suggest_selling_price-foc_item-{$foc_pa_item.id}" />
	</td>
	
	<!-- Purchase Price  -->
	<td align="center">
		<input type="text" name="purchase_price[foc_item][{$foc_pa_item.id}]" size="5" value="{$foc_pa_item.purchase_price|number_format:$config.global_cost_decimal_points:".":""|ifzero:''}" class="required" title="Purhcase Price" onChange="PURCHASE_AGREEMENT_MODULE.item_price_changed(this);" id="purchase_price-foc_item-{$foc_pa_item.id}" />
		<input type="hidden" name="cost[foc_item][{$foc_pa_item.id}]" size="5" value="{$foc_pa_item.cost|number_format:$config.global_cost_decimal_points:".":""}" />
		<br />
		<span class="span_latest_cost">{$foc_pa_item.cost|number_format:$config.global_cost_decimal_points}</span>
	</td>
	
	<!-- Allowed Branches -->
	<td {if $allow_edit}onmouseover="$('div_allowed_branches_popup-foc_item-{$foc_pa_item.id}').show();" onmouseout="$('div_allowed_branches_popup-foc_item-{$foc_pa_item.id}').hide();"{/if}>
		<div id="div_allowed_branches_popup-foc_item-{$foc_pa_item.id}" class="div_allowed_branches_popup" style="display:none;">
			<ul class="ul_allowed_branches">
				<li><span><input type="checkbox" id="inp_toggle_all_branch-foc_item-{$foc_pa_item.id}" onChange="PURCHASE_AGREEMENT_MODULE.toggle_allowed_branches('foc_item', '{$foc_pa_item.id}');" /> All</span></li>
				{foreach from=$branches_list key=bid item=b}
					<li>
						<span><input type="checkbox" class="inp_allowed_branches-foc_item-{$foc_pa_item.id}" name="allowed_branches[foc_item][{$foc_pa_item.id}][{$bid}]" value="1" {if $foc_pa_item.allowed_branches.$bid}checked {/if} title="{$b.code}" onChange="PURCHASE_AGREEMENT_MODULE.update_allowed_branches_label('foc_item', '{$foc_pa_item.id}');" /> {$b.code}</span>
					</li>
				{/foreach}
			</ul>
		</div>
		
		<div id="div_allowed_branches_details-foc_item-{$foc_pa_item.id}">
			{if $foc_pa_item.allowed_branches}
				{assign var=tmp_branch_listed value=0}
				
				{foreach from=$branches_list key=bid item=b name=fb}
					{if $foc_pa_item.allowed_branches.$bid}
						{if $tmp_branch_listed}, {/if}
						{assign var=tmp_branch_listed value=1}
						{$b.code}
					{/if}
				{/foreach}
			{else}
				-- NONE --			
			{/if}
		</div>
		
		{*<span><input type="checkbox" id="inp_toggle_all_branch-foc_item-{$foc_pa_item.id}" onChange="PURCHASE_AGREEMENT_MODULE.toggle_allowed_branches('foc_item', '{$foc_pa_item.id}');" /> All</span>
		{foreach from=$branches_list key=bid item=b}
			<span><input type="checkbox" class="inp_allowed_branches-foc_item-{$foc_pa_item.id}" name="allowed_branches[foc_item][{$foc_pa_item.id}][{$bid}]" value="1" {if $foc_pa_item.allowed_branches.$bid}checked {/if} /> {$b.code}</span>
		{/foreach}*}
	</td>
</tr>