{*
7/13/2012 4:26:34 PM Justin
- Fixed bug of showing extra "-" on price type while in view mode.

8/16/2012 10:44 AM Andy
- Add when change price will also automatically copy price to item under same parent/child, same uom, same price type. (need config sku_change_price_always_apply_to_same_uom)

8/16/2012 2:57 PM Andy
- Add GP % and checking only show if got privilege.
- Add need same artno only can copy price.

5/20/2014 10:12 AM Fithri
- able to select item(s) to reject & must provide reason for each rejected item

7/17/2014 3:03 PM Justin
- Enhanced to have GP, GP(%) and Variance calculation.

10/9/2014 4:19 PM Justin
- Enhanced to have GST calculation.

10/29/2014 4:01 PM Justin
- Enhanced GST information to show in a formal table.

3/21/2015 4:05 PM Justin
- Enhanced to have "Show More Items" button that will prompt new tab for user to view items in full.

3/21/2015 5:07 PM Justin
- Enhanced to hide all input fields while showing full items.

4/7/2016 10:46 AM Andy
- Fix "Current GP" to use selling price before gst.

6/14/2017 13:41 Qiu Ying
- Bug fixed on price cannot be copied when item added by "Add All Price Type"

11/8/2018 11:59 AM Justin
- Enhanced to highlight the item row when it is called from SKU Change Price.
*}

<tr id="item_{$item.id}" {if $item.is_deleted}style="display:none;"{/if} class="tr_item_row {if $smarty.request.highlight_item_id eq $item.id || $smarty.request.highlight_sku_item_id eq $item.sku_item_id}highlight_row{/if}">
	<td nowrap>
		{if !$readonly}
			<img src="ui/remove16.png" class="clickable" title="Delete Row" onclick="MST_FUTURE_PRICE_MODULE.delete_item('{$item.id}', '');" align="absmiddle" alt="{$item.id}">
		{/if}
		<span class="row_no" id="row_no_{$item.id}">{$row_no}.</span>
	</td>
	{if $form.status eq 1 && !$form.approved && $form.approval_screen and $config.sku_change_price_approval_allow_reject_by_items}
	<td align="left"><input name="rejected_item_id[{$item.id}]" type="checkbox" value="1" onclick="reject_cb_clicked(this)" class="rejected_item_cb" /><span style="display:none;">&nbsp;&nbsp;<input name="rejected_item_reason[{$item.id}]" type="text" size="10" placeholder="Reason" /></span></td>
	{/if}
	<td>{$item.sku_item_code}</td>
	<td align="center">{$item.artno|default:"-"}</td>
	<td align="center">{$item.mcode|default:"-"}</td>
	<td>
		{$item.description}
		{if $form.id < 1000000000 and $item.extra_info.reject_reason and $form.status==0 and $config.sku_change_price_approval_allow_reject_by_items}
		<br /><span style="color:red;font-size:x-small;"><b>( Reject reason : {$item.extra_info.reject_reason} )</b></span>
		{/if}
	</td>
	<td align="right">{$item.stock_bal|qty_nf}</td>
	{if $sessioninfo.privilege.SHOW_COST}
		<td class="r">{$item.cost|number_format:$config.global_cost_decimal_points}</td>
	{/if}
	<td class="r" nowrap>
		{$item.selling_price|number_format:2}
		{if $gst_settings}
			{if $item.inclusive_tax eq 'yes'}
				<div class="div_sp_excl">Excl: {$item.selling_price_before_gst|number_format:2}</div>
			{/if}
		{/if}
	</td>
	<td align="center">
		{if !$is_show_full_items}
			<select name="type[{$item.id}]" onchange="MST_FUTURE_PRICE_MODULE.type_changed('{$item.id}', this);" {if $form.approval_screen}disabled{/if}>
				<option value="normal" {if $item.type eq "normal"}selected{/if}>Normal</option>
				<option value="qprice" {if $item.type eq "qprice"}selected{/if}>QPrice</option>
				{foreach from=$config.sku_multiple_selling_price key=r item=mt}
					<option value="{$mt}" {if $item.type eq $mt}selected{/if}>{$mt}</option>
				{/foreach}
			</select>
		{else}
			{$item.type|strtoupper}
		{/if}
	</td>
	<td align="center">
		{if $item.sku_type ne 'CONSIGN' || ($form.approval_screen || $readonly) && ($item.sku_type ne 'CONSIGN' || $item.type eq 'qprice')}
			-
		{else}
			{if !$is_show_full_items}
				<select name="trade_discount_code[{$item.id}]" {if $item.sku_type ne 'CONSIGN' || $item.type eq 'qprice' || $form.approval_screen}style="display:none;" disabled{/if}>
					{foreach from=$discount_codes key=row item=pct}
						<option value="{$pct.code}" {if $item.trade_discount_code eq $pct.code}selected{/if}>{$pct.code}</option>
					{/foreach}
				</select>
				<span id="span_tdc_{$item.id}" {if ($item.sku_type eq 'CONSIGN' && $item.type ne 'qprice') || $form.approval_screen || $readonly}style="display:none;"{/if}>-</span>
			{else}
				{$item.trade_discount_code}
			{/if}
		{/if}
	</td>
	<td align="center">
		{if !$is_show_full_items}
			<input name="min_qty[{$item.id}]" value="{$item.min_qty}" class="r" size="5" {if $item.type ne "qprice"}style="display:none;" disabled{/if} onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" {if $form.approval_screen}disabled{/if}>
			<span id="span_min_qty_{$item.id}" {if $item.type eq "qprice"}style="display:none;"{/if}>-</span>
		{else}
			{$item.min_qty}
		{/if}
	</td>
	<td align="center">
		{if $gst_settings}
			{assign var=selling_price value=$item.future_selling_price}
			{if $item.inclusive_tax eq "yes"}
				{assign var=tmp_gst_rate value=$item.gst_rate+100}
				{assign var=gst_selling_price value=$selling_price*100/$tmp_gst_rate}
				{assign var=gst_amount value=$gst_selling_price*$item.gst_rate/100}
			{else}
				{assign var=gst_amount value=$selling_price*$item.gst_rate/100}
				{assign var=gst_selling_price value=$selling_price+$gst_amount}
			{/if}
			{assign var=gst_selling_price value=$gst_selling_price|round:2}
			<table>
				<tr>
					<td nowrap><font color="blue"><b>Before GST</b></font></td>
					<td>
						{if $item.inclusive_tax eq 'yes'}
							{if !$is_show_full_items}
								<input name="gst_selling_price[{$item.id}]" value="{$gst_selling_price|number_format:2:'.':''}" size="5" class="r" onchange="mf(this);MST_FUTURE_PRICE_MODULE.calculate_gst({$item.id}, this);MST_FUTURE_PRICE_MODULE.propose_price_changed('{$item.id}');" {if $form.approval_screen}disabled{/if}>
							{else}
								{$gst_selling_price|number_format:2}
							{/if}
						{else}
							{if !$is_show_full_items}
								<input name="future_selling_price[{$item.id}]" value="{$item.future_selling_price|number_format:2:'.':''}" size="5" class="r future_selling_price" onchange="mf(this);MST_FUTURE_PRICE_MODULE.calculate_gst({$item.id}, this);MST_FUTURE_PRICE_MODULE.propose_price_changed('{$item.id}');" {if $form.approval_screen}disabled{/if}>
							{else}
								{$item.future_selling_price|number_format:2}
							{/if}
						{/if}
					</td>
				</tr>
				<tr>
					<td nowrap><font color="blue"><b>GST ({$item.gst_rate|default:'0'}%)</b></font></td>
					<td>
						{if !$is_show_full_items}
							<input name="gst_amount[{$item.id}]" value="{$gst_amount|number_format:2:'.':''}" size="5" class="r" readonly />
						{else}
							{$gst_amount|number_format:2}
						{/if}
					</td>
				</tr>
				<tr>
					<td nowrap><font color="blue"><b>After GST</b></font></td>
					<td>
						{if $item.inclusive_tax eq 'yes'}
							{if !$is_show_full_items}
								<input name="future_selling_price[{$item.id}]" value="{$item.future_selling_price|number_format:2:'.':''}" size="5" class="r future_selling_price" onchange="mf(this);MST_FUTURE_PRICE_MODULE.calculate_gst({$item.id}, this);MST_FUTURE_PRICE_MODULE.propose_price_changed('{$item.id}');" {if $form.approval_screen}disabled{/if}>
							{else}
								{$item.future_selling_price|number_format:2}
							{/if}
						{else}
							{if !$is_show_full_items}
								<input name="gst_selling_price[{$item.id}]" value="{$gst_selling_price|number_format:2:'.':''}" size="5" class="r" onchange="mf(this);MST_FUTURE_PRICE_MODULE.calculate_gst({$item.id}, this);MST_FUTURE_PRICE_MODULE.propose_price_changed('{$item.id}');" {if $form.approval_screen}disabled{/if}>
							{else}
								{$gst_selling_price|number_format:2}
							{/if}
						{/if}
					</td>
				</tr>
			</table>
		{else}
			{if !$is_show_full_items}
				<input name="future_selling_price[{$item.id}]" value="{$item.future_selling_price|number_format:2:'.':''}" size="5" class="r" onchange="mf(this);MST_FUTURE_PRICE_MODULE.calculate_gst({$item.id}, this);MST_FUTURE_PRICE_MODULE.propose_price_changed('{$item.id}');" {if $form.approval_screen}disabled{/if}>
			{else}
				{$item.future_selling_price|number_format:2}
			{/if}
		{/if}
		
		{if !$is_show_full_items}
			<input type="hidden" name="temp_id[{$item.id}]" value="{$item.id}" />
			<input type="hidden" name="row_no[{$item.id}]" value="{$item.row_no|default:$smarty.foreach.i.iteration}" />
			<input type="hidden" class="isi_id" id="si_id_{$item.id}" name="si_id[{$item.id}]" value="{$item.sku_item_id}" />
			<input type="hidden" name="si_code[{$item.id}]" value="{$item.sku_item_code}" />
			<input type="hidden" name="si_artno[{$item.id}]" value="{$item.artno}" />
			<input type="hidden" name="si_mcode[{$item.id}]" value="{$item.mcode}" />
			<input type="hidden" name="si_description[{$item.id}]" value="{$item.description|escape:'html'}" />
			<input type="hidden" name="si_sku_type[{$item.id}]" value="{$item.sku_type|escape:'html'}" />
			<input type="hidden" name="si_sku_id[{$item.id}]" value="{$item.sku_id}" />
			<input type="hidden" name="si_packing_uom_fraction[{$item.id}]" value="{$item.packing_uom_fraction}" />
			<input type="hidden" name="cost[{$item.id}]" value="{$item.cost}" />
			<input type="hidden" name="selling_price[{$item.id}]" value="{$item.selling_price}" />
			<input type="hidden" name="selling_price_before_gst[{$item.id}]" value="{$item.selling_price_before_gst}" />
			<input type="hidden" name="is_deleted[{$item.id}]" value="{$item.is_deleted}" />
			<input type="hidden" name="inclusive_tax[{$item.id}]" value="{$item.inclusive_tax}" />
			<input type="hidden" name="gst_id[{$item.id}]" value="{$item.gst_id}" />
			<input type="hidden" name="gst_rate[{$item.id}]" value="{$item.gst_rate}" />
			<input type="hidden" name="gst_code[{$item.id}]" value="{$item.gst_code}" />
		{/if}
	</td>
	
	{if $sessioninfo.privilege.SHOW_COST}
		{assign var=curr_gp value=0}
		{assign var=curr_gp_per value=0}
		{assign var=curr_sp value=$item.selling_price}
		
		{if $gst_settings}
			{assign var=curr_sp value=$item.selling_price_before_gst}
		{/if}
		
		{if $curr_sp}
			{assign var=curr_gp value=$curr_sp-$item.cost}
			{assign var=curr_gp_per value=$curr_gp/$curr_sp*100}
		{/if}
		
		<td class="r" style="color:{if $curr_gp < 0}red{elseif $curr_gp > 0}green{else}green{/if};">
			{$curr_gp|number_format:4}
		</td>
		<td class="r" style="color:{if $curr_gp < 0}red{elseif $curr_gp > 0}green{else}green{/if};">
			{$curr_gp_per|number_format:2}
		</td>
		{assign var=new_gp value=0}
		{assign var=new_gp_per value=0}

		{if $gst_settings && $item.inclusive_tax eq 'yes' && $item.gst_rate > 0}
			{assign var=gp_selling_price value=$gst_selling_price}
		{else}
			{assign var=gp_selling_price value=$item.future_selling_price}
		{/if}
		
		{if $gp_selling_price}
			{assign var=new_gp value=$gp_selling_price-$item.cost}
			{assign var=new_gp_per value=$new_gp/$gp_selling_price*100}
		{/if}
		<td class="r" id="td_gp-{$item.id}" style="color:{if $new_gp < 0}red{elseif $new_gp > 0}green{else}black{/if};">
			{$new_gp|number_format:4}
		</td>
		<td class="r" id="td_gp_per-{$item.id}" style="color:{if $new_gp < 0}red{elseif $new_gp > 0}green{else}black{/if};">
			{$new_gp_per|number_format:2}
		</td>
		
		{assign var=gp_var value=0}
		{assign var=gp_per_var value=0}
			
		{if $gp_selling_price}
			{assign var=gp_var value=$new_gp-$curr_gp}
			{assign var=gp_per_var value=$gp_var/$gp_selling_price*100}
		{/if}
		<td class="r" id="td_gp_var-{$item.id}" style="color:{if $gp_var < 0}red{elseif $gp_var > 0}green{else}black{/if};">
			{$gp_var|number_format:4}
		</td>
		<td class="r" id="td_gp_per_var-{$item.id}" style="color:{if $gp_var < 0}red{elseif $gp_var > 0}green{else}black{/if};">
			{$gp_per_var|number_format:2}
		</td>
	{/if}
</tr>
