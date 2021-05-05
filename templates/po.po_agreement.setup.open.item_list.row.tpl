{*
8/17/2012 4:08 PM Andy
- Change allowed branches list to edit in popup

9/24/2012 11:08 AM Andy
- Add artno.
*}

<tr id="tr_item_row-{$pa_item.id}" class="tr_item_row">
	<td nowrap>
		<span class="span_no">{$smarty.foreach.fitem.iteration}</span>
		<input type="hidden" name="sku_item_id[item][{$pa_item.id}]" value="{$pa_item.sku_item_id}" />
		
		{if $allow_edit}
			<img src="/ui/del.png" align="absmiddle" title="Delete" class="clickable" onClick="PURCHASE_AGREEMENT_MODULE.delete_item('{$pa_item.id}');" />
		{/if}
	</td>
	<td id="td_sku_item_code-item-{$pa_item.id}">{$pa_item.sku_item_code|default:'-'}</td>
	<td id="td_mcode-item-{$pa_item.id}">{$pa_item.mcode|default:'-'}</td>
	<td id="td_artno-item-{$pa_item.id}">{$pa_item.artno|default:'-'}</td>
	<td id="td_desc-item-{$pa_item.id}">{$pa_item.description|default:'-'}</td>
	
	<!-- rule num -->
	<td align="center">
		<input type="hidden" name="rule_num[item][{$pa_item.id}]" value="{$pa_item.rule_num}" class="inp_rule_num" id="inp_rule_num-{$pa_item.id}" />
		{$pa_item.rule_num|default:'-'}
	
	</td>
	
	<!-- Qty Type -->
	<td align="center">
		<select name="qty_type[item][{$pa_item.id}]" onChange="PURCHASE_AGREEMENT_MODULE.qty_type_changed('{$pa_item.id}');" id="sel_qty_type-{$pa_item.id}">
			{foreach from=$pa_qty_type_list key=k item=v}
				<option value="{$k}" {if $pa_item.qty_type eq $k}selected {/if}>{$v}</option>
			{/foreach}
		</select>
	</td>
	
	<!-- Qty -->
	<td align="center" nowrap>
		<input type="text" name="qty1[item][{$pa_item.id}]" size="5" value="{$pa_item.qty1|ifzero:''}" class="required" title="Qty" onChange="PURCHASE_AGREEMENT_MODULE.qty_changed(this);" id="inp-qty1-item-{$pa_item.id}"/>
		
		<span id="span_qty2-{$pa_item.id}" style="{if $pa_item.qty_type ne 'range'}display:none;{/if}">
			to
			<input type="text" name="qty2[item][{$pa_item.id}]" size="5" value="{$pa_item.qty2|ifzero:''}" class="{if $pa_item.qty_type eq 'range'}required{/if}" title="Qty To" onChange="PURCHASE_AGREEMENT_MODULE.qty_changed(this);" id="inp-qty2-item-{$pa_item.id}" />
		</span>
	</td>
	
	<!-- Discount -->
	<td align="center">
		<input type="text" name="discount[item][{$pa_item.id}]" size="8" value="{$pa_item.discount}" onChange="PURCHASE_AGREEMENT_MODULE.item_discount_changed('{$pa_item.id}');" id="inp_discount-item-{$pa_item.id}" />
	</td>
	
	<!-- Suggested Selling Price -->
	<td align="center">
		<input type="text" name="suggest_selling_price[item][{$pa_item.id}]" size="5" value="{$pa_item.suggest_selling_price|number_format:2:".":""|ifzero:''}" class="required" title="Suggested Selling Price" onChange="PURCHASE_AGREEMENT_MODULE.item_price_changed(this);" id="suggest_selling_price-item-{$pa_item.id}" />
	</td>
	
	<!-- Purchase Price -->
	<td align="center">
		<input type="text" name="purchase_price[item][{$pa_item.id}]" size="5" value="{$pa_item.purchase_price|number_format:$config.global_cost_decimal_points:".":""|ifzero:''}" class="required" title="Purhcase Price" onChange="PURCHASE_AGREEMENT_MODULE.item_price_changed(this);" id="purchase_price-item-{$pa_item.id}" />
		<input type="hidden" name="cost[item][{$pa_item.id}]" size="5" value="{$pa_item.cost|number_format:$config.global_cost_decimal_points:".":""}" />
		<br />
		<span class="span_latest_cost">{$pa_item.cost|number_format:$config.global_cost_decimal_points}</span>
	</td>
	
	<!-- Allowed Branches -->
	<td {if $allow_edit}onmouseover="$('div_allowed_branches_popup-item-{$pa_item.id}').show();" onmouseout="$('div_allowed_branches_popup-item-{$pa_item.id}').hide();"{/if}>
		<div id="div_allowed_branches_popup-item-{$pa_item.id}" class="div_allowed_branches_popup" style="display:none;">
			<ul class="ul_allowed_branches">
				<li><span><input type="checkbox" id="inp_toggle_all_branch-item-{$pa_item.id}" onChange="PURCHASE_AGREEMENT_MODULE.toggle_allowed_branches('item', '{$pa_item.id}');" /> All</span></li>
				{foreach from=$branches_list key=bid item=b}
					<li>
						<span><input type="checkbox" class="inp_allowed_branches-item-{$pa_item.id}" name="allowed_branches[item][{$pa_item.id}][{$bid}]" value="1" {if $pa_item.allowed_branches.$bid}checked {/if} title="{$b.code}" onChange="PURCHASE_AGREEMENT_MODULE.update_allowed_branches_label('item', '{$pa_item.id}');" /> {$b.code}</span>
					</li>
				{/foreach}
			</ul>
		</div>
		
		<div id="div_allowed_branches_details-item-{$pa_item.id}">
			{if $pa_item.allowed_branches}
				{assign var=tmp_branch_listed value=0}
				
				{foreach from=$branches_list key=bid item=b name=fb}
					{if $pa_item.allowed_branches.$bid}
						{if $tmp_branch_listed}, {/if}
						{assign var=tmp_branch_listed value=1}
						{$b.code}
					{/if}
				{/foreach}
			{else}
				-- NONE --			
			{/if}
		</div>
		{*
		<span><input type="checkbox" id="inp_toggle_all_branch-item-{$pa_item.id}" onChange="PURCHASE_AGREEMENT_MODULE.toggle_allowed_branches('item', '{$pa_item.id}');" /> All</span>
		{foreach from=$branches_list key=bid item=b}
			<span><input type="checkbox" class="inp_allowed_branches-item-{$pa_item.id}" name="allowed_branches[item][{$pa_item.id}][{$bid}]" value="1" {if $pa_item.allowed_branches.$bid}checked {/if} /> {$b.code}</span>
		{/foreach>
		*}
	</td>
	
	<!-- GP % -->
	<td class="r">
		{*
		{assign var=gp value=0}
		{if $pa_item.suggest_selling_price}
			{assign var=gp value=$pa_item.suggest_selling_price-$pa_item.purchase_price}
			{assign var=gp value=$gp/$pa_item.suggest_selling_price*100}
		{/if}
		*}
		
		<span id="span_gp_per-item-{$pa_item.id}">{$gp|number_format:2}</span>%
	</td>
</tr>
