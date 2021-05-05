{*
4/10/2013 5:08 PM Andy
- Remove sheet discount and not allow user to edit item discount.

5/29/2013 2:48 PM Andy
- Enhance to can delete item.
*}

{assign var=cost value=$item.cost_price}
{assign var=selling value=$item.selling_price}

{assign var=qty value=$item.ctn*$item.uom_fraction+$item.pcs}
{assign var=amount value=$selling/$item.uom_fraction}
{assign var=amount value=$amount*$qty}

{if $item.item_discount_amount}
	{assign var=amount value=$amount-$item.item_discount_amount}
{/if}

{assign var=item_id value=$item.id}

<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="tr_item-{$item_id}" class="tr_item">
    <td align="center" nowrap width="50">
    	{* Action icon *}
		<img src="ui/remove16.png" align="absmiddle" title="Delete" class="clickable" onClick="SALES_ORDER.delete_item_clicked('{$item_id}');" />
		
		{* no *}
		<span class="span_no" id="span_no-{$item_id}">{$smarty.foreach.f.iteration}.</span>
		
		<input type="hidden" name="item_list[{$item_id}][sku_item_id]" value="{$item.sku_item_id}" />
		<input type="hidden" name="item_list[{$item_id}][doc_allow_decimal]" value="{$item.doc_allow_decimal}" />
		<input type="hidden" name="item_list[{$item_id}][bom_ref_num]" value="{$item.bom_ref_num}" />
		<input type="hidden" name="item_list[{$item_id}][bom_qty_ratio]" value="{$item.bom_qty_ratio}" />
		<input type="hidden" name="item_list[{$item_id}][do_qty]" value="{$item.do_qty}" />
		<input type="hidden" name="item_list[{$item_id}][cost_price]" value="{$cost}" />
		<input type="hidden" name="item_list[{$item_id}][stock_balance]" value="{$item.stock_balance}" />
		
		<input type="hidden" id="inp_row_qty-{$item_id}" value="{$qty}" />
		<input type="hidden" id="inp_row_amt-{$item_id}" value="{$amount}" />
		
		
	</td>
	<td>{$item.sku_item_code|default:'&nbsp;'}</td>
	<td>{$item.artno_mcode|default:'&nbsp;'}</td>
	<td>{$item.sku_description|default:'&nbsp;'} {include file=details.uom.tpl uom=$item.packing_uom_code}</td>
	
	{* Selling *}
	<td align="center">
		<input class="r inp_selling" id="inp_selling_price-{$item_id}" name="item_list[{$item_id}][selling_price]" value="{$selling|number_format:2:".":""}" size="8" onchange="SALES_ORDER.inp_selling_changed('{$item_id}');" />
	</td>
	
	{* UOM *}
	<td align="center">
	    <input type="hidden" name="item_list[{$item_id}][uom_id]" id="uom_id-{$item_id}" value="{$item.uom_id|default:1}" />
	    <input type="hidden" name="item_list[{$item_id}][uom_fraction]" id="uom_fraction-{$item_id}" value="{$item.uom_fraction|default:1}" />
	    
	    {*<select name="sel_uom[{$item.id}]" onchange="uom_change(this.value,'{$item.id}');" {if $readonly or ($config.doc_uom_control)}disabled {/if}>
	        {foreach from=$uom key=uom_id item=u}
	            <option value="{$uom_id},{$u.fraction}" {if ($item.uom_id eq $uom_id) or (!$item.uom_id and $u.code eq 'EACH')}selected {/if}>{$u.code}</option>
	        {/foreach}
	    </select>*}
	    {$uom[$item.uom_id].code|default:'EACH'}
	</td>
	
	{* Qty - CTN *}
	<td align="center" nowrap valign="top">
		<input class="r inp_ctn {if $item.doc_allow_decimal}inp_doc_allow_decimal{/if}" id="inp_ctn-{$item_id}" name="item_list[{$item_id}][ctn]" {if $item.uom_fraction == 1 or $item.uom_id==1 or !$item.uom_id}disabled {/if} value="{$item.ctn}" onchange="SALES_ORDER.inp_ctn_changed('{$item_id}')" placeholder="--" />
	</td>
	
	{* Qty - PCS *}
	<td align="center" nowrap valign="top">
		<input class="r inp_pcs {if $item.doc_allow_decimal}inp_doc_allow_decimal{/if}" id="inp_pcs-{$item_id}" name="item_list[{$item_id}][pcs]" style="background:#fc9;" onchange="SALES_ORDER.inp_pcs_changed('{$item_id}');" value="{$item.pcs}" />
	</td>
	
	{* Row Total Qty *}
	<td align="right">
		<span id="span_row_qty-{$item_id}" class="inp_row_qty">{$qty|qty_nf|default:0}</span>
	</td>
	
	{* Discount *}
	<td align="center">
		<input type="hidden" name="item_list[{$item_id}][item_discount_amount]" id="inp_item_discount_amount-{$item_id}" value="{$item.item_discount_amount}" />
		
		<input type="hidden" name="item_list[{$item_id}][item_discount]" size="10" onChange="SALES_ORDER.item_discount_changed('{$item.id}');" id="inp_item_discount-{$item_id}" value="{$item.item_discount}" />
		
		{$item.item_discount|default:'-'}
	</td>
	
	{* Row Total Amount*}
	<td align="right">
		<span id="span_row_amount-{$item_id}" class="span_row_amt">{$amount|default:0|number_format:2:".":""}</span>
	</td>
</tr>
