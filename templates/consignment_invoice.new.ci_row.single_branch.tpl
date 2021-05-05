{*
8/4/2009 1:11:13 PM Andy
- add selling price changes function

2/12/2010 5:19:11 PM Andy
- Add new consignment type: consignment over invoice

7/14/2010 2:53:42 PM Andy
- Add settings for consignment invoice.
- Able to control whether use item discount or not.
- Able to control whether split invoice by price type or not when confirm.

3/30/2011 12:14:40 PM Justin
- Modified the cost price to round to 3 instead of 2 decimal points.

3/26/2012 11:23:43 AM Justin
- Renamed the "price_type_id" become "price_type".

9/14/2012 4:39 PM Drkoay
- change row_recalc to row_recalc(this) due to function rewrite

3/26/2015 3:18 PM Justin
- Enhanced to have new feature that calculate from GST price into net price.

5/13/2015 5:48 PM Andy
- Change the ajax add item to use json instead of xml.
- Enhanced to have display cost price feature.

5/19/2015 10:04 AM Andy
- Change to always show latest artno/mcode for item.
- Fix cost price onchange function duplicated.

6/4/2015 10:43 AM Andy
- Fix foreign discount calculation.

6/19/2015 3:41 PM Andy
- Fix cost price rounding issue when no GST.

8/27/2015 3:04 PM Andy
- Add an input to hold item sku_item_id.
*}

<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" id="titem{$item.id}" 
	class="{if $smarty.request.highlight_item_id eq $item.sku_item_id}highlight_row{/if} tr_ci_item_row"
	item_id="{$item.id}">

{literal}
<style>
input[disabled],input[readonly],select[disabled], textarea[disabled]{
  color:black;
}
</style>
{/literal}


{assign var=row_no value=$item_n|default:$smarty.foreach.fitem.iteration}
<td align=center nowrap width=50>
<span class="sku_item_info" id="sku_item_id,{$item.sku_item_id},{$item.id}"></span>
<input type="hidden" name="item_sku_item_id[{$item.id}]" value="{$item.sku_item_id}" class="inp_item_sku_item_id" />

{if $form.create_type ne '3' && ($form.status<1 || $form.status eq '2') && !$form.approval_screen && !$readonly}
<img src=ui/remove16.png class=clickable title="Delete Row" onclick="delete_item({$item.id})" align=absmiddle alt="{$item.id}">
{/if}
<span class="no" id="no_{$smarty.foreach.fitem.iteration}">
{$smarty.foreach.fitem.iteration}.</span>

<input type="hidden" name="price_type[{$item.id}]" value="{$item.price_type}" id="price_type,{$item.id}" />
</td>

{assign var=bid value=$form.ci_branch_id|default:"0"}

<td align=center>{$item.sku_item_code}</td>
<td nowrap>{$item.artno|default:$item.mcode}</td>
<td><sup style="color:blue;" id="trade_discount_code,{$item.id}">{$item.trade_discount_code}</sup>{$item.description}</td>

{if $form.create_type==3}
	<td align=right>
	<input class="r cost uom" id=po_cost_{$item.id} name=po_cost[{$item.id}] value="{$item.po_cost|number_format:2:".":""}" size=6 title="{$bid},{$item.id}" onchange="mf(this);" {if $readonly}disabled{/if}>
	</td>
{/if}
<td align=right><input size=5 class="small r" readonly name="stock_balance[{$item.id}]" id="stock_balance,{$item.id}" value="{$item.stock_balance}" style="background:#ddd;"></td>

{* Cost Price *}
<td nowrap align="right" valign="bottom">
	{assign var=cost value=$item.cost_price|default:$item.grn_cost|default:$item.po_cost|default:$item.master_cost}
	{assign var=foreign_cost value=$item.foreign_cost_price|number_format:3:".":""}

	{assign var=display_cost_price value=$item.display_cost_price|round:3}
	{if $display_cost_price<=0}{assign var=display_cost_price value=$cost|round:3}{/if}
	
	{if $form.is_under_gst}
		<div style="white-space: nowrap;" class="small">
			<input type="checkbox" name="display_cost_price_is_inclusive[{$item.id}]" value="1" title="Ticked = Price is Inclusive Tax" 
			onChange="CI_MODULE.display_cost_price_is_inclusive_changed('{$item.id}');" 
			{if $item.display_cost_price_is_inclusive}checked {/if} />
			<span>
				<input size="8" name="display_cost_price[{$item.id}]" onChange="CI_MODULE.display_cost_price_changed('{$item.id}');"
				{if $readonly}disabled{/if} {if $form.exchange_rate && $form.exchange_rate ne '1'}readonly{/if}
				value="{$display_cost_price|number_format:3:'.':''}" />
			</span>
		</div>
	{/if}
	
	<div id="div_cost_price_info-{$item.id}" style="{if $form.is_under_gst && !$item.display_cost_price_is_inclusive}display:none;{/if}">
		<input class="r cost uom" id="cost_price_{$item.id}" name="cost_price[{$item.id}]" value="{if $form.is_under_gst}{$cost}{else}{$cost|number_format:2:'.':''}{/if}" size="6" title="{$bid},{$item.id}" 
		{if $readonly}disabled{/if} {if $form.exchange_rate && $form.exchange_rate ne '1'}readonly{/if} 
		type="{if $form.is_under_gst}hidden{else}text{/if}"  onChange="CI_MODULE.cost_price_changed('{$item.id}', true);"/>
	
		<span id="span_cost_price_label-{$item.id}" class="small" style="color:blue;{if !$form.is_under_gst}display:none;{/if}">{$cost|number_format:4:'.':''}</span>
	</div>
	
	
	{* if !$readonly && $form.is_under_gst}<img src="ui/icons/money_dollar.png" style="margin-top:3px; margin-bottom:-3px; cursor:pointer;" onclick="toggle_process_gst_price('{$item.id}');" />{/if *}
</td>

{if $config.consignment_modules && $config.consignment_multiple_currency}
	<td align=right class="foreign_cost_price" {if $hide_currency_field}style="display:none;"{/if}>
	<input class="r cost uom" id="foreign_cost_price_{$item.id}" name="foreign_cost_price[{$item.id}]" value="{$foreign_cost}" size=6 title="{$bid},{$item.id}" 
	onchange="CI_MODULE.foreign_cost_price_changed('{$item.id}');" {if $readonly}disabled{/if} {if $form.price_indicate eq '1'}readonly{/if}>
	</td>
{/if}

<td align=center>
<input type=hidden name=no_item value="{$no_item}">
<input type=hidden name=uom_id[{$item.id}] id=uom_id{$item.id} value="{$item.uom_id|default:1}" item_id="{$item.id}" class="tr_item">
<input type=hidden name=uom_fraction[{$item.id}] class="uom" title="{$item.id}" id=uom_fraction{$item.id} value="{$item.uom_fraction|default:1}">

{*<select name=sel_uom[{$item.id}] id="sel_uom{$item.id}" onchange="uom_change(this.value,'{$item.id}');row_recalc(this,{$item.id},'')" {if $readonly}disabled{/if}>*}
  <select name=sel_uom[{$item.id}] id="sel_uom{$item.id}" onchange="CI_MODULE.item_uom_changed('{$item.id}');" {if $readonly}disabled{/if}>

{section name=i loop=$uom}
<option value="{$uom[i].id},{$uom[i].fraction}" {if $item.uom_id == $uom[i].id or ($item.uom_id==0 and $uom[i].code eq 'EACH')}selected{/if}>{$uom[i].code}</option>
{/section}
</select>
</td>


{*if !$form.open_info.name*}
<td align=right>
<input name="selling_price[{$item.id}]" size=4 value="{$item.selling_price|default:'-'|number_format:3:".":""}" class="r uom" {if !$form.allow_edit_selling_price}readonly {/if} id="selling_price_{$item.id}" title="{$bid},{$item.id}" onChange="selling_price_changed(this);">
</td>
{*/if*}

{assign var=qty value=0}

{assign var=total_qty value=0}
{assign var=qty value=$qty+$item.ctn*$item.uom_fraction+$item.pcs}
{assign var=amount value=$cost/$item.uom_fraction}
{assign var=amount value=$amount*$qty}
{assign var=selling value=$item.selling_price*$qty}

{if $form.show_per}
	{if $item.disc_arr.0}
	    {assign var=discount_amount value=$amount*$item.disc_arr.0/100}
	    {assign var=amount value=$amount-$discount_amount}
	{/if}
	{if $item.disc_arr.1}
	    {assign var=discount_amount value=$amount*$item.disc_arr.1/100}
	    {assign var=amount value=$amount-$discount_amount}
	{/if}
{/if}

<!--input type=hidden name=ci_item_id[{$item.id}] value="{$item.id}"-->

<td align=center nowrap valign=top>
{*<input class="r uom" id="qty_ctn{$item.id}_{$bid}" title="{$bid},{$item.id}" name="qty_ctn[{$item.id}]" {if $item.uom_fraction == 1 or $item.uom_id==1 or !$item.uom_id}disabled value="--"{else} value="{$item.ctn}"{/if} style="width:30px;" size=1 onchange="row_recalc(this,{$item.id},{$bid})" {if $readonly}disabled{/if}>*}
<input class="r uom inp_qty_ctn" id="qty_ctn{$item.id}_{$bid}" title="{$bid},{$item.id}" name="qty_ctn[{$item.id}]" {if $item.uom_fraction == 1 or $item.uom_id==1 or !$item.uom_id}disabled value="--"{else} value="{$item.ctn}"{/if} style="width:30px;" size=1 onchange="row_recalc(this)" {if $readonly}disabled{/if}
 />

{*<input class="r uom" id="qty_pcs{$item.id}_{$bid}" title="{$bid},{$item.id}" name="qty_pcs[{$item.id}]" style="width:30px; background:#fc9;" size=1 onchange="row_recalc(this,{$item.id},{$bid})" value="{$item.pcs}" {if $readonly}disabled{/if}>*}
<input class="r uom inp_qty_pcs" id="qty_pcs{$item.id}_{$bid}" title="{$bid},{$item.id}" name="qty_pcs[{$item.id}]" style="width:30px; background:#fc9;" size=1 onchange="row_recalc(this)" value="{$item.pcs}" {if $readonly}disabled{/if}
 />
</td>

<td align=right>
	<span id=row_qty{$item.id} class="uom" title=",{$item.id}">{$qty|default:0}</span>
</td>

<td align="right">
    <span id="row_discount,{$item.id}" class="uom row_discount">{$item.discount|default:0}</span>%
    <input type="hidden" class="row_discount" name="discount_per[{$item.id}]" id="input_discount_per,{$item.id}" value="{$item.discount|default:0}" />
	<input type="hidden" name="item_disc_amt[{$item.id}]" id="inp_item_disc_amt-{$item.id}" value="{$item.item_disc_amt}" />
	<input type="hidden" name="item_foreign_disc_amt[{$item.id}]" value="{$item.item_foreign_disc_amt}" class="row_foreign_gross_disc_amt" />
</td>

<td align=right>
	<span id=row_selling{$item.id} class="uom" title=",{$item.id}">{$selling|default:0|number_format:2:".":""}</span>
</td>
<td align=right>
	<span id=row_amount{$item.id} class="item_amt" title=",{$item.id}">{$amount|default:0|number_format:2:".":""}</span>
	<input type="hidden" name="item_amt[{$item.id}]" value="{$amount}"/>
	<input type="hidden" name="item_foreign_amt[{$item.id}]" value="{$item.item_foreign_amt}" class="row_foreign_gross_amt" />
	
	<input type="hidden" name="item_gst_amt[{$item.id}]" value="{$item.item_gst_amt}"/>
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
		<select name="item_gst_sel[{$item.id}]" onchange="CI_MODULE.item_gst_changed('{$item.id}')"{if $readonly || $form.is_export}disabled {/if} class="item_gst_slt" item_id="{$item.id}">
			{foreach from=$gst_list item=gst key=dum}
				<option gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" gst_indicator="{$gst.indicator_receipt}" {if $item.gst_id eq $gst.id and $item.gst_code eq $gst.code and $item.gst_rate eq $gst.rate}selected {/if} value="{$gst.id}">
					{$gst.code} ({$gst.rate}%)
				</option>
			{/foreach}
		</select>
	</td>

	{* GST Amount *}
	<td align="right">
		<span id="row_gst{$item.id}" class="item_gst">
			{$row_gst|number_format:2}
		</span>
	</td>
	
	{* amount include GST *}
	<td align="right">
		<input type="hidden" name="gst_id[{$item.id}]" value="{$item.gst_id}" />
		<input type="hidden" name="gst_code[{$item.id}]" value="{$item.gst_code}" />
		<input type="hidden" name="gst_rate[{$item.id}]" value="{$item.gst_rate}" />
		<input type="hidden" name="gst_indicator[{$item.id}]" value="{$item.gst_indicator}" />

		<input type="hidden" name="item_gst[{$item.id}]" value="{$item.item_gst}"/>
		<input type="hidden" name="item_gst2[{$item.id}]" value="{$item.item_gst2}"/>
		
		{if $config.consignment_modules && $config.consignment_multiple_currency}
			<input type="hidden" name="item_foreign_gst[{$item.id}]" value="{$item.item_foreign_gst}" />
			
			<input type="hidden" name="item_foreign_gst2[{$item.id}]" value="{$item.item_foreign_gst2}"/>
		{/if}

		<span id="item_gst_amt{$item.id}" class="item_gst_amt">{$item_gst_amt|default:0|number_format:2:".":""}</span>
	</td>
{/if}

{if $config.consignment_modules && is_array($config.consignment_multiple_currency)}
	<td align=right class="row_foreign_amount" {if $hide_currency_field}style="display:none;"{/if}>
		<span id="row_foreign_amount{$item.id}" class="uom row_foreign_amt" title=",{$item.id}">{$foreign_amount|default:0|number_format:2:".":""}</span>
		
		<input type="hidden" name="item_foreign_gst_amt[{$item.id}]" value="{$item.item_foreign_gst_amt}"/>
		<input type="hidden" name="item_foreign_amt2[{$item.id}]" value="{$item.item_foreign_amt2}"/>
		<input type="hidden" name="item_foreign_disc_amt2[{$item.id}]" value="{$item.item_foreign_disc_amt2}"/>
		<input type="hidden" name="item_foreign_gst_amt2[{$item.id}]" value="{$item.item_foreign_gst_amt2}"/>
	</td>
{/if}
</tr>