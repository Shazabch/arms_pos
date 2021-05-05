{*
12/26/2012 10:24 AM Andy
- Add show link code at "DO Request (Add by SKU Group)".

1/10/2012 3:09 PM Andy
- Add show packing uom at sku group item list.

2/19/2013 11:41 AM Justin
- Modified the qty round up to base on config set.

3/7/2013 3:22 PM Andy
- Enhanced to pre-load Stock Balance From at DO Request (Add by SKU Group).
*}

<table width="100%" class="report_table">
	<tr class="header">
		<th>ARMS Code</th>
		<th>MCode</th>
		<th>Art No.</th>
		<th>{$config.link_code_name}</th>
		<th>Description</th>
		<th>UOM</th>
		<th>Stock balance</th>
		<th>Request Qty</th>
	</tr>
	
	{foreach from=$sgi_items item=r}
		<tr id="tr_sgi_item_row-{$r.sid}" class="tr_sgi_item_row">
			<td>
				<input type="hidden" name="sku_item_code_list[{$r.sid}]" value="{$r.sku_item_code}" />
				{$r.sku_item_code}
			</td>
			<td>{$r.mcode|default:'-'}</td>
			<td>{$r.artno|default:'-'}</td>
			<td>{$r.link_code|default:'-'}</td>
			<td>{$r.description|default:'-'}</td>
			<td>{$r.packing_uom_code|default:'-'}</td>
			<td align="center"><input type="text" name="stock_balance_list[{$r.sid}]" size="5" class="inp_stock_balance_list" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" value="{$r.stock_balance}" style="text-align:right;" /></td>
			<td align="center"><input type="text" name="request_qty_list[{$r.sid}]" size="5" class="inp_request_qty_list" onChange="{if $r.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" /></td>
		</tr>
	{/foreach}
</table>

<p align="center" id="p_action_button">
	<input type="button" value="Add Item to DO Request" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onClick="DO_REQUEST_BY_SKU_GROUP.add_do_request_clicked();" id="btn_add_do_request" />
</p>

<div id="div_result" style="background-color:yellow;display:none;" class="stdframe">

</div>