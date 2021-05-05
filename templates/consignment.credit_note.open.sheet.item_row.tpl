{*
11/3/2011 4:51:54 PM Justin
- Added "span" area for trade discount code changing when deliver branch has been changed.
- Added hidden field "cost".

12/15/2011 3:07:32 PM Justin
- Added foreign cost price and row amount columns.

1/21/2015 5:55 PM Justin
- Enhanced to have GST calculation.

3/26/2015 3:18 PM Justin
- Enhanced to have new feature that calculate from GST price into net price.

6/9/2015 1:56 PM Andy
- Enhanced to have display cost price feature for CN/DN.
- Enhanced CN/DN recalculation.

6/19/2015 3:41 PM Andy
- Fix cost price rounding issue when no GST.
*}

{assign var=cost value=$item.cost_price}
{assign var=foreign_cost value=$item.foreign_cost_price|number_format:3:".":""}

{assign var=display_cost_price value=$item.display_cost_price|round:3}
{if $display_cost_price<=0}{assign var=display_cost_price value=$cost|round:3}{/if}
{if !$form.is_under_gst}{assign var=cost value=$cost|round:3}{/if}

{assign var=selling value=$item.selling_price}

{assign var=qty value=$item.ctn*$item.uom_fraction+$item.pcs}

{assign var=row_selling value=$selling/$item.uom_fraction}
{assign var=row_selling value=$row_selling*$qty}

<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="tr_item,{$item.id}" class="tr_item {if $smarty.request.highlight_item_id eq $item.sku_item_id}highlight_row{/if}" item_id="{$item.id}">
    <td align=center nowrap width=50>
		{if !$readonly}
		<img src=ui/remove16.png class=clickable title="Delete Row" onclick="delete_item('{$item.id}')" align="absmiddle" alt="{$item.id}">
		{/if}
		<span class="no">{$smarty.foreach.f.iteration}.</span>
		<input type="hidden" class="inp_price_type_id" id="price_type_id,{$item.id}" name="price_type_id[{$item.id}]" value="{$item.price_type_id}" />
	</td>
	<td>{$item.sku_item_code}</td>
	<td>{$item.artno_mcode}</td>
	<td>
		<sup style="color:blue"><span id="tdc,{$item.id}">{$item.trade_discount_code}</span></sup>
		{$item.sku_description}
	</td>
	<td align="center"><input size="6" class="small r" readonly name="stock_balance1[{$item.id}]" id="stock_balance1,{$item.id}" value="{$item.stock_balance1}" style="background:#ddd;" sku_item_id="{$item.sku_item_id}" /></td>
	<td align="center"><input size="6" class="small r" readonly name="stock_balance2[{$item.id}]" id="stock_balance2,{$item.id}" value="{$item.stock_balance2}" style="background:#ddd;" sku_item_id="{$item.sku_item_id}" /></td>
	
	{* COST PRICE *}
	<td nowrap align="center">
		{if $form.is_under_gst}
			<div style="white-space: nowrap;" class="small">
				<input type="checkbox" name="display_cost_price_is_inclusive[{$item.id}]" value="1" title="Ticked = Price is Inclusive Tax" 
				onChange="CN_MODULE.display_cost_price_is_inclusive_changed('{$item.id}');" 
				{if $item.display_cost_price_is_inclusive}checked {/if} />
				<span>
					<input size="8" name="display_cost_price[{$item.id}]" onChange="CN_MODULE.display_cost_price_changed('{$item.id}');"
					{if $readonly}disabled{/if} {if $form.exchange_rate && $form.exchange_rate ne '1'}readonly{/if}
					value="{$display_cost_price|number_format:3:'.':''}" class="inp_display_cost_price" />
				</span>
			</div>
		{/if}
	
		<div id="div_cost_price_info-{$item.id}" style="{if $form.is_under_gst && !$item.display_cost_price_is_inclusive}display:none;{/if}">
			<input class="r cost inp_cost_price" id="cost_price,{$item.id}" name="cost_price[{$item.id}]" value="{if $form.is_under_gst}{$cost}{else}{$cost|number_format:3:'.':''}{/if}" size="6" title="{$bid},{$item.id}" {if $readonly}disabled{/if} {if $form.exchange_rate && $form.exchange_rate ne '1'}readonly{/if} 
			type="{if $form.is_under_gst}hidden{else}text{/if}"  onChange="CN_MODULE.cost_price_changed('{$item.id}', true);"/>
		
			<span id="span_cost_price_label-{$item.id}" class="small" style="color:blue;{if !$form.is_under_gst}display:none;{/if}">{$cost|number_format:4:'.':''}</span>
		</div>
		
		{* if !$readonly && $form.is_under_gst}<img src="ui/icons/money_dollar.png" style="margin-top:3px; margin-bottom:-3px; cursor:pointer;" onclick="toggle_process_gst_price('{$item.id}');" />{/if *}
	</td>

	{if $config.consignment_modules && $config.consignment_multiple_currency}
		<td align=right class="foreign_cost_price" {if $hide_currency_field}style="display:none;"{/if}>
			<input class="r cost uom inp_foreign_cost_price" id="foreign_cost_price,{$item.id}" name="foreign_cost_price[{$item.id}]" value="{$foreign_cost}" size=6 title="{$bid},{$item.id}" 
			onchange="CN_MODULE.foreign_cost_price_changed('{$item.id}');" {if $readonly}disabled{/if} />
		</td>
	{/if}
	
	<td align="center"><input class="r selling" id="selling_price,{$item.id}" name="selling_price[{$item.id}]" value="{$selling|number_format:2:".":""}" size="8" readonly /></td>
	<td align="center">
	    <input type="hidden" name="uom_id[{$item.id}]" id="uom_id,{$item.id}" value="{$item.uom_id|default:1}" item_id="{$item.id}" class="tr_items" />
	    <input type="hidden" name="uom_fraction[{$item.id}]" id="uom_fraction,{$item.id}" value="{$item.uom_fraction|default:1}" />
	    <input type="hidden" name="cost[{$item.id}]" id="cost,{$item.id}" value="{$item.cost|default:0}" />
	    <select name="sel_uom[{$item.id}]" id="sel_uom{$item.id}" onchange="CN_MODULE.item_uom_changed('{$item.id}');" {if $readonly or ($config.doc_uom_control)}disabled {/if}>
	        {foreach from=$uom key=uom_id item=u}
	            <option value="{$uom_id},{$u.fraction}" {if ($item.uom_id eq $uom_id) or (!$item.uom_id and $u.code eq 'EACH')}selected {/if}>{$u.code}</option>
	        {/foreach}
	    </select>
	</td>
	<td align="center" nowrap valign="top">
		<input class="r ctn inp_qty_ctn" id="ctn,{$item.id}" name="ctn[{$item.id}]" {if $item.uom_fraction == 1 or $item.uom_id==1 or !$item.uom_id}disabled value="--"{else} value="{$item.ctn}"{/if} style="width:30px;" size="1" onchange="row_recalc('{$item.id}');" {if $readonly}disabled {/if} />
	</td>
	<td align="center" nowrap valign="top">
	    <input class="r pcs inp_qty_pcs" id="pcs,{$item.id}" name="pcs[{$item.id}]" style="width:30px; background:#fc9;" size="1" onchange="row_recalc('{$item.id}');" value="{$item.pcs}" {if $readonly}disabled {/if} />
	</td>
	<td align="right">
		<span id="row_qty,{$item.id}" class="row_qty">{$qty|default:0}</span>
	</td>
	<td align=right>
	    <span id="row_discount,{$item.id}" class="row_discount">{$item.discount_per|default:0}</span>%
	    <input type="hidden" name="discount_per[{$item.id}]" id="input_discount_per,{$item.id}" value="{$item.discount_per|default:0}" />
	    <input type="hidden" name="discount_amt[{$item.id}]" id="input_discount_amt,{$item.id}" value="{$item.discount_amt|default:0}" />
		<input type="hidden" name="foreign_discount_amt[{$item.id}]" id="input_foreign_discount_amt,{$item.id}" value="{$item.discount_foreign_amt|default:0}" class="row_foreign_gross_disc_amt" />
	</td>
	<td align=right>
		<span id="row_selling,{$item.id}" class="row_selling">{$row_selling|default:0|number_format:2:".":""}</span>
	</td>
	<td align="right">
		<span id="row_amount,{$item.id}" class="row_amt">{$item.item_amt|default:0|number_format:2:".":""}</span>
		<input type="hidden" name="item_amt[{$item.id}]" value="{$item.item_amt}"/>
		<input type="hidden" name="item_foreign_amt[{$item.id}]" value="{$item.item_foreign_amt}" class="item_foreign_gross_amt"/>
		
		<input type="hidden" name="item_gst_amt[{$item.id}]" value="{$item.item_gst_amt}" class="row_gst_amt"/>
		
		<input type="hidden" name="item_amt2[{$item.id}]" value="{$item.item_amt2}"/>
		<input type="hidden" name="item_disc_amt2[{$item.id}]" value="{$item.item_disc_amt2}"/>
		<input type="hidden" name="item_gst_amt2[{$item.id}]" value="{$item.item_gst_amt2}"/>
	</td>
	{if $form.is_under_gst}
		{assign var=gst_amt value=0}
		{assign var=row_gst value=$amount*$item.gst_rate/100}
		{assign var=row_gst value=$row_gst|round2}
		{assign var=row_gst_amt value=$amount+$row_gst}

		{* GST Selection *}
		<td align="center">
			<select name="item_gst_sel[{$item.id}]" onchange="CN_MODULE.item_gst_changed('{$item.id}')" {if $readonly || $form.is_export}disabled {/if} class="item_gst_slt" item_id="{$item.id}">
				{foreach from=$gst_list item=gst key=dum}
					<option gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" gst_indicator="{$gst.indicator_receipt}" {if $item.gst_id eq $gst.id and $item.gst_code eq $gst.code and $item.gst_rate eq $gst.rate}selected {/if} value="{$gst.id}">
						{$gst.code} ({$gst.rate}%)
					</option>
				{/foreach}
			</select>
		</td>

		{* GST Amount *}
		<td align="right">
			<span id="row_gst,{$item.id}" class="item_gst">
				{$row_gst|number_format:2}
			</span>
		</td>
		
		{* amount include GST *}
		<td align="right">
			<input type="hidden" name="gst_id[{$item.id}]" value="{$item.gst_id}" />
			<input type="hidden" name="gst_code[{$item.id}]" value="{$item.gst_code}" />
			<input type="hidden" name="gst_rate[{$item.id}]" value="{$item.gst_rate}" />
			<input type="hidden" name="gst_indicator[{$item.id}]" value="{$item.gst_indicator}" />

			<input type="hidden" name="item_gst[{$item.id}]" value="{$item.item_gst}" class="row_item_gst"/>

			<input type="hidden" name="item_gst2[{$item.id}]" value="{$item.item_gst2}"/>
			
			<span id="item_gst_amt,{$item.id}" class="item_gst_amt">{$item.item_gst_amt|default:0|number_format:2:".":""}</span>
		</td>
	{/if}
	{if $config.consignment_modules && is_array($config.consignment_multiple_currency)}
		<td align=right class="row_foreign_amount" {if $hide_currency_field}style="display:none;"{/if}>
			<span id="row_foreign_amount,{$item.id}" class="uom row_foreign_amt">
				{$item.item_foreign_gst_amt|default:$item.item_foreign_amt|default:0|number_format:2:".":""}
			</span>
			
			{if $form.is_under_gst}
				<input type="hidden" name="item_foreign_gst[{$item.id}]" value="{$item.item_foreign_gst}"/>
				<input type="hidden" name="item_foreign_gst2[{$item.id}]" value="{$item.item_foreign_gst2}"/>
			{/if}
			
			<input type="hidden" name="item_foreign_amt2[{$item.id}]" value="{$item.item_foreign_amt2}"/>
			<input type="hidden" name="item_foreign_disc_amt2[{$item.id}]" value="{$item.item_foreign_disc_amt2}"/>
			
			<input type="hidden" name="item_foreign_gst_amt[{$item.id}]" value="{$item.item_foreign_gst_amt}" id="item_foreign_gst_amt,{$item.id}"/>
			<input type="hidden" name="item_foreign_gst_amt2[{$item.id}]" value="{$item.item_foreign_gst_amt2}"/>			
		</td>
	{/if}
</tr>
