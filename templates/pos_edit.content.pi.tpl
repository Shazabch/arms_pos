{*
10/15/2012 9:26 AM Andy
- Add can swap new pos items row.
- Add when edit/remove pos items row will recalculate total qty and amount.

8/26/2013 5:37 PM Justin
- Enhanced to round qty to 3 decimal points.
*}

{if $pi and !$pi_id}
	{assign var=pi_id value=$pi.id}
{/if}

<tr id="tr_pi_item-{$pi_id}" class="tr_pi_item">
	<td align="center">
		<input type="hidden" name="pi_id[{$pi_id}]" value="{$pi_id}" />
		
		<!-- Delete -->
		{if is_new_id($pi_id) or $pi_id eq '__TMP_PI_ID__'}
			<img src="/ui/del.png" align="absmiddle" class="clickable" onClick="POS_FORM.delete_pi_clicked('{$pi_id}');" />	
		{else}
			<input type="checkbox" name="pi_delete[{$pi_id}]" value="1" onChange="POS_FORM.delete_pi_checked('{$pi_id}');" />
		{/if}
	</td>
	
	<td>
		<img src="ui/icons/arrow_up.png" title="Move Up" align="absmiddle" class="clickable img_pi_swap_up" id="img_pi_swap_up-{$pi_id}" onClick="POS_FORM.swap_pi_row('up','{$pi_id}');" />
		<img src="ui/icons/arrow_down.png" title="Move Down" align="absmiddle" class="clickable img_pi_swap_down" id="img_pi_swap_down-{$pi_id}" onClick="POS_FORM.swap_pi_row('down', '{$pi_id}');" />
	</td>
	
	<!-- ARMS Code -->
	<td>
		<input type="text" size="15" name="pi_sku_item_code[{$pi_id}]" id="pi_sku_item_code-{$pi_id}" value="{$pi.sku_item_code}" />
	</td>
	
	<!-- Barcode -->
	<td>
		<input type="text" size="25" name="pi_barcode[{$pi_id}]" id="pi_barcode-{$pi_id}" value="{$pi.barcode}" />
		<img src="/ui/barcode.png" align="top" align="absmiddle" class="clickable" onClick="POS_FORM.barcode_clicked({$pi_id});" title="Auto Search Barcode" />
	</td>
	
	<!-- Item ID -->
	<td>
		<input type="text" size="5" name="pi_item_id[{$pi_id}]" class="inp_pi_item_id" value="{$pi.item_id}" />
	</td>
	
	<!-- Qty -->
	<td>
		<input type="text" size="5" name="pi_qty[{$pi_id}]" id="pi_qty-{$pi_id}" value="{$pi.qty}" onChange="mfz(this, 3);POS_FORM.pi_qty_changed('{$pi_id}');" />
	</td>
	
	<!-- Price -->
	<td>
		<input type="text" size="8" name="pi_price[{$pi_id}]" id="pi_price-{$pi_id}" value="{$pi.price}" onChange="mfz(this);POS_FORM.pi_price_changed('{$pi_id}');" />
	</td>
	
	<!-- Discount -->
	<td>
		<input type="text" size="8" name="pi_discount[{$pi_id}]" value="{$pi.discount}" onChange="mfz(this);POS_FORM.pi_discount_changed('{$pi_id}');" />
	</td>
</tr>