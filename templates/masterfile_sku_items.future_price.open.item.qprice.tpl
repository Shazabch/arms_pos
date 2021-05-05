{*
7/27/2012 3:13 PM Justin
- Bug fixed on row number.

8/16/2012 2:57 PM Andy
- Add GP % and checking only show if got privilege.

7/17/2014 3:03 PM Justin
- Enhanced to have GP, GP(%) and Variance calculation.

10/9/2014 4:19 PM Justin
- Enhanced to have GST calculation.

10/29/2014 4:01 PM Justin
- Enhanced GST information to show in a formal table.

3/21/2015 4:06 PM Justin
- Bug fixed on javascript errors while changing min qty.

3/21/2015 5:07 PM Justin
- Enhanced to hide all input fields while showing full items.

4/7/2016 10:46 AM Andy
- Fix "Current GP" to use selling price before gst.

11/8/2018 11:59 AM Justin
- Enhanced to highlight the item row when it is called from SKU Change Price.
*}
{assign var=si_id value=$form.sku_item_id|default:$item.sku_item_id}
{if !$form.is_sub_item}
	<tr id="qprice_item_{$item.id}" {if $item.is_deleted}style="display:none;"{/if}>
		<td valign="bottom">
			{if !$readonly}
				<input type="button" value="+" id="qprice_btn_{$item.id}" title="Add sub item for QPrice" onclick="MST_FUTURE_PRICE_MODULE.ajax_add_qprice_item('{$si_id}', '{$item.id}');" style="background:#ea1;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;">
			{else}
				&nbsp;
			{/if}
		</td>
		<td colspan="17">
			<table id="qprice_tbl_{$item.id}" style="background: #eaeaea !important; border: 1px #aaaeee solid;" width="100%">
				<thead>
					<tr height="32" bgcolor="#aecdae" class="small">
						<th rowspan="2">#</th>
						<th rowspan="2">ARMS</th>
						<th rowspan="2">Artno</th>
						<th rowspan="2">Mcode</th>
						<th rowspan="2">Description</th>
						<th rowspan="2">Stock<br />Balance</th>
						{if $sessioninfo.privilege.SHOW_COST}
							<th rowspan="2">Cost</th>
						{/if}
						<th rowspan="2">Price</th>
						<th rowspan="2">Min Qty<br />(QPrice)</th>
						<th rowspan="2">Proposed<br />Price</th>
						{if $sessioninfo.privilege.SHOW_COST}
							<th colspan="2">Current</th>
							<th colspan="2">New</th>
							<th colspan="2">Variance</th>
						{/if}
					</tr>
					{if $sessioninfo.privilege.SHOW_COST}
						<tr height="32" bgcolor="#aecdae" class="small">
							<th>GP</th>
							<th>GP(%)</th>
							<th>GP</th>
							<th>GP(%)</th>
							<th>GP</th>
							<th>GP(%)</th>
						</tr>
					{/if}
				</thead>
{/if}
{foreach from=$form.qprice_items.$si_id key=r item=qpitem name=i}
	<tr id="item_{$qpitem.id}" {if $smarty.request.highlight_item_id eq $qpitem.id || $smarty.request.highlight_sku_item_id eq $qpitem.sku_item_id}class="highlight_row"{/if} {if $qpitem.is_deleted}style="display:none;"{else}{$row_count++}{/if} >
		<td nowrap>
			{if !$readonly}
				<img src="ui/remove16.png" class="clickable" title="Delete Row" onclick="MST_FUTURE_PRICE_MODULE.delete_item('{$qpitem.id}', '{$item.id}');" align="absmiddle" alt="{$qpitem.id}">
			{/if}
			<span class="qprice_row_no" id="qprice_row_no_{$qpitem.id}">{$row_count}.</span>
		</td>
		<td>{$qpitem.sku_item_code}</td>
		<td align="center">{$qpitem.artno|default:"-"}</td>
		<td align="center">{$qpitem.mcode|default:"-"}</td>
		<td>{$qpitem.description}</td>
		<td align="right">{$qpitem.stock_bal|qty_nf}</td>
		{if $sessioninfo.privilege.SHOW_COST}
			<td class="r">{$qpitem.cost|number_format:$config.global_cost_decimal_points}</td>
		{/if}
		<td class="r">
			{$qpitem.selling_price|number_format:2}
			{if $gst_settings}
				{if $qpitem.inclusive_tax eq 'yes'}
					<div class="div_sp_excl">Excl: {$qpitem.selling_price_before_gst|number_format:2}</div>
				{/if}
			{/if}
		</td>
		<td align="center">
			{if !$is_show_full_items}
				<input name="min_qty[{$qpitem.id}]" value="{$qpitem.min_qty}" class="r" size="5" onchange="{if $qpitem.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" {if $form.approval_screen}disabled{/if}>
			{else}
				{$qpitem.min_qty}
			{/if}
		</td>
		<td align="center">
			{if $gst_settings}
				{assign var=selling_price value=$qpitem.future_selling_price}
				{if $qpitem.inclusive_tax eq "yes"}
					{assign var=tmp_gst_rate value=$qpitem.gst_rate+100}
					{assign var=gst_selling_price value=$selling_price*100/$tmp_gst_rate}
					{assign var=gst_amount value=$gst_selling_price*$qpitem.gst_rate/100}
				{else}
					{assign var=gst_amount value=$selling_price*$qpitem.gst_rate/100}
					{assign var=gst_selling_price value=$selling_price+$gst_amount}
				{/if}
				{assign var=gst_selling_price value=$gst_selling_price|round:2}
				<table>
					<tr>
						<td nowrap><font color="blue"><b>Before GST</b></font></td>
						<td>
							{if $qpitem.inclusive_tax eq 'yes'}
								{if !$is_show_full_items}
									<input name="gst_selling_price[{$qpitem.id}]" value="{$gst_selling_price|number_format:2:'.':''}" size="5" class="r" onchange="mf(this);MST_FUTURE_PRICE_MODULE.calculate_gst({$qpitem.id}, this);MST_FUTURE_PRICE_MODULE.propose_price_changed('{$qpitem.id}');" {if $form.approval_screen}disabled{/if}>
								{else}
									{$gst_selling_price|number_format:2}
								{/if}
							{else}
								{if !$is_show_full_items}
									<input name="future_selling_price[{$qpitem.id}]" value="{$qpitem.future_selling_price|number_format:2:'.':''}" size="5" class="r future_selling_price" onchange="mf(this);MST_FUTURE_PRICE_MODULE.calculate_gst({$qpitem.id}, this);MST_FUTURE_PRICE_MODULE.propose_qprice_changed('{$qpitem.id}');" {if $form.approval_screen}disabled{/if}>
								{else}
									{$qpitem.future_selling_price|number_format:2}
								{/if}
							{/if}
						</td>
					</tr>
					<tr nowrap>
						<td><font color="blue"><b>GST ({$qpitem.gst_rate|default:'0'}%)</b></font></td>
						<td>
							{if !$is_show_full_items}
								<input name="gst_amount[{$qpitem.id}]" value="{$gst_amount|number_format:2:'.':''}" size="5" class="r" readonly />
							{else}
								{$gst_amount|number_format:2}
							{/if}
						</td>
					</tr>
					<tr>
						<td nowrap><font color="blue"><b>After GST</b></font></td>
						<td>
							{if $qpitem.inclusive_tax eq 'yes'}
								{if !$is_show_full_items}
									<input name="future_selling_price[{$qpitem.id}]" value="{$qpitem.future_selling_price|number_format:2:'.':''}" size="5" class="r future_selling_price" onchange="mf(this);MST_FUTURE_PRICE_MODULE.calculate_gst({$qpitem.id}, this);MST_FUTURE_PRICE_MODULE.propose_qprice_changed('{$qpitem.id}');" {if $form.approval_screen}disabled{/if}>
								{else}
									{$qpitem.future_selling_price|number_format:2}
								{/if}
							{else}
								{if !$is_show_full_items}
									<input name="gst_selling_price[{$qpitem.id}]" value="{$gst_selling_price|number_format:2:'.':''}" size="5" class="r" onchange="mf(this);MST_FUTURE_PRICE_MODULE.calculate_gst({$qpitem.id}, this);MST_FUTURE_PRICE_MODULE.propose_price_changed('{$qpitem.id}');" {if $form.approval_screen}disabled{/if}>
								{else}
									{$gst_selling_price|number_format:2}
								{/if}
							{/if}
						</td>
					</tr>
				</table>
			{else}
				{if !$is_show_full_items}
					<input name="future_selling_price[{$qpitem.id}]" value="{$qpitem.future_selling_price|number_format:2:'.':''}" size="5" class="r" onchange="mf(this);MST_FUTURE_PRICE_MODULE.calculate_gst({$qpitem.id}, this);MST_FUTURE_PRICE_MODULE.propose_qprice_changed('{$qpitem.id}');" {if $form.approval_screen}disabled{/if}>
				{else}
					{$qpitem.future_selling_price|number_format:2}
				{/if}
			{/if}
			{if !$is_show_full_items}
				<input type="hidden" name="type[{$qpitem.id}]" value="{$qpitem.type}" />
				<input type="hidden" name="row_no[{$qpitem.id}]" value="{$qpitem.row_no|default:$smarty.foreach.i.iteration}" />
				<input type="hidden" name="si_id[{$qpitem.id}]" value="{$qpitem.sku_item_id}" />
				<input type="hidden" name="si_code[{$qpitem.id}]" value="{$qpitem.sku_item_code}" />
				<input type="hidden" name="si_artno[{$qpitem.id}]" value="{$qpitem.artno}" />
				<input type="hidden" name="si_mcode[{$qpitem.id}]" value="{$qpitem.mcode}" />
				<input type="hidden" name="si_description[{$qpitem.id}]" value="{$qpitem.description|escape:'html'}" />
				<input type="hidden" name="si_sku_type[{$qpitem.id}]" value="{$qpitem.sku_type|escape:'html'}" />
				<input type="hidden" name="si_sku_id[{$qpitem.id}]" value="{$qpitem.sku_id}" />
				<input type="hidden" name="si_packing_uom_fraction[{$qpitem.id}]" value="{$qpitem.packing_uom_fraction}" />
				<input type="hidden" name="cost[{$qpitem.id}]" value="{$qpitem.cost}" />
				<input type="hidden" name="selling_price[{$qpitem.id}]" value="{$qpitem.selling_price}" />
				<input type="hidden" name="selling_price_before_gst[{$qpitem.id}]" value="{$qpitem.selling_price_before_gst}" />
				<input type="hidden" class="qprice_deleted" name="is_deleted[{$qpitem.id}]" value="{$qpitem.is_deleted}" />
				<input type="hidden" name="is_qprice[{$qpitem.id}]" id="is_qprice_{$qpitem.id}" value="1" />
				<input type="hidden" name="type[{$qpitem.id}]" value="qprice" />
				<input type="hidden" name="inclusive_tax[{$qpitem.id}]" value="{$qpitem.inclusive_tax}" />
				<input type="hidden" name="gst_rate[{$qpitem.id}]" value="{$qpitem.gst_rate}" />
				<input type="hidden" name="gst_code[{$qpitem.id}]" value="{$qpitem.gst_code}" />
			{/if}
		</td>

		{if $sessioninfo.privilege.SHOW_COST}
			{assign var=curr_gp value=0}
			{assign var=curr_gp_per value=0}
			{assign var=curr_sp value=$qpitem.selling_price}
			
			{if $gst_settings}
				{assign var=curr_sp value=$qpitem.selling_price_before_gst}
			{/if}
		
			{if $curr_sp}
				{assign var=curr_gp value=$curr_sp-$qpitem.cost}
				{assign var=curr_gp_per value=$curr_gp/$curr_sp*100}
			{/if}
			<td class="r" style="color:{if $curr_gp < 0}red{elseif $curr_gp > 0}green{else}green{/if};">
				{$curr_gp|number_format:4}
			</td>
			<td class="r" style="color:{if $curr_gp < 0}red{elseif $curr_gp > 0}green{else}green{/if};">
				{$curr_gp_per|number_format:2}
			</td>
			
			{if $gst_settings && $item.inclusive_tax eq 'yes' && $item.gst_rate > 0}
				{assign var=gp_selling_price value=$gst_selling_price}
			{else}
				{assign var=gp_selling_price value=$qpitem.future_selling_price}
			{/if}
			
			{assign var=new_gp value=0}
			{assign var=new_gp_per value=0}
			{if $qpitem.future_selling_price}
				{assign var=new_gp value=$gp_selling_price-$qpitem.cost}
				{assign var=new_gp_per value=$new_gp/$gp_selling_price*100}
			{/if}
			<td class="r" id="td_gp-{$qpitem.id}" style="color:{if $new_gp < 0}red{elseif $new_gp > 0}green{else}black{/if};">
				{$new_gp|number_format:4}
			</td>
			<td class="r" id="td_gp_per-{$qpitem.id}" style="color:{if $new_gp < 0}red{elseif $new_gp > 0}green{else}black{/if};">
				{$new_gp_per|number_format:2}
			</td>
			
			{assign var=gp_var value=0}
			{assign var=gp_per_var value=0}
				
			{if $gp_selling_price}
				{assign var=gp_var value=$new_gp-$curr_gp}
				{assign var=gp_per_var value=$gp_var/$gp_selling_price*100}
			{/if}
			<td class="r" id="td_gp_var-{$qpitem.id}" style="color:{if $gp_var < 0}red{elseif $gp_var > 0}green{else}black{/if};">
				{$gp_var|number_format:4}
			</td>
			<td class="r" id="td_gp_per_var-{$qpitem.id}" style="color:{if $gp_var < 0}red{elseif $gp_var > 0}green{else}black{/if};">
				{$gp_per_var|number_format:2}
			</td>
		{/if}
	</tr>
{/foreach}
{if !$form.is_sub_item}
			</table>
		</td>
	</tr>
{/if}
